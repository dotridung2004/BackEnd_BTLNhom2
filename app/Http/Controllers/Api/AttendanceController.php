<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; // Thêm import này
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\User; // Giả sử sinh viên cũng là User
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Dùng cho transaction
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    // public function index()
    // {
    //     // Sử dụng Cache::remember để lưu cache trong 60 giây
    //     $attendances = Cache::remember('attendances', 60, function () {
    //         return Attendance::with('category')->get();
    //     });

    //     return response()->json($attendances, 200);
    // }

    // /**
    //  * Tạo một sản phẩm mới
    //  */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => ['required', 'string', 'max:255', 'unique:products,name'],
    //         'price' => ['required', 'numeric', 'min:0'],
    //         'description' => ['required', 'string', 'max:500'],
    //         'category_id' => ['required', 'exists:categories,id'],
    //     ]);

    //     $product = Attendance::create($request->only(['name', 'price', 'description', 'category_id']));

    //     // Xóa cache cũ khi có thay đổi
    //     Cache::forget('products');

    //     $product->load('category');
    //     return response()->json($product, 201);
    // }

    // /**
    //  * Lấy thông tin một sản phẩm cụ thể kèm category
    //  */
    // public function show(Attendance $attendance)
    // {
    //     $attendance->load('category');
    //     return response()->json($attendance, 200);
    // }

    // /**
    //  * Cập nhật thông tin sản phẩm
    //  */
    // public function update(Request $request, Attendance $product)
    // {
    //     $request->validate([
    //         'name' => ['sometimes', 'string', 'max:255', Rule::unique('products', 'name')->ignore($product->id)],
    //         'price' => ['sometimes', 'numeric', 'min:0'],
    //         'description' => ['sometimes', 'string', 'max:500'],
    //         'category_id' => ['sometimes', 'exists:categories,id'],
    //     ]);

    //     $product->update($request->only(['name', 'price', 'description', 'category_id']));

    //     // Xóa cache khi có thay đổi
    //     Cache::forget('products');

    //     $product->load('category');
    //     return response()->json($product, 200);
    // }

    // /**
    //  * Xóa một sản phẩm
    //  */
    // public function destroy(Product $product)
    // {
    //     $product->delete();

    //     // Xóa cache khi có thay đổi
    //     Cache::forget('products');

    //     return response()->json(null, 204);
    // }
    public function getStudentsAndAttendance(Request $request, Schedule $schedule)
    {
        $request->validate(['date' => 'required|date_format:Y-m-d']);
        // Chuyển thành chuỗi YYYY-MM-DD để so sánh với DB
        $date = Carbon::parse($request->query('date'))->toDateString();

        // --- QUAN TRỌNG: Điều chỉnh query này dựa trên cách bạn liên kết sinh viên ---
        // Ví dụ này giả định sinh viên được liên kết thông qua 'classModel' (bảng classes)
        // và ClassModel có quan hệ many-to-many tên là students().
        // Bạn có thể cần bảng trung gian 'class_student'.

        // 1. Lấy danh sách sinh viên của lớp học này
        //    (Đảm bảo Model ClassModel có hàm students() trả về quan hệ many-to-many)
        $students = $schedule->classCourseAssignment // Lấy phân công
                              ->classModel // Lấy lớp học (ClassModel) từ phân công đó
                              ?->students() // Gọi quan hệ students() trên ClassModel
                              ->orderBy('name', 'asc') // Sắp xếp theo tên
                              ->get(['users.id', 'users.name']); // Chỉ lấy ID và tên

        // Xử lý nếu không tìm thấy lớp hoặc lớp không có sinh viên
        if (!$students) {
            return response()->json([]); // Trả về mảng rỗng nếu không có lớp hoặc sinh viên
        }

        // 2. Lấy các bản ghi điểm danh đã có của những sinh viên này
        //    cho đúng lịch dạy và ngày này
        $existingAttendance = Attendance::where('schedule_id', $schedule->id)
                                        ->where('date', $date) // Lọc đúng ngày
                                        ->whereIn('student_id', $students->pluck('id')) // Chỉ lấy SV trong danh sách
                                        ->pluck('status', 'student_id'); // Tạo map [student_id => status]

        // 3. Kết hợp thông tin sinh viên với trạng thái điểm danh
        $results = $students->map(function($student) use ($existingAttendance) {
            return [
                // Đảm bảo key khớp với model Student.dart
                'student_id'   => $student->id, // Dùng 'id' làm ID sinh viên
                'student_name' => $student->name,
                // Mặc định là 'present' nếu chưa có bản ghi, ngược lại lấy status đã lưu
                'status'       => $existingAttendance->get($student->id, 'present')
            ];
        });

        return response()->json($results);
    }

    /**
     * Lưu điểm danh hàng loạt
     */
    public function saveBulkAttendance(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'date'        => 'required|date_format:Y-m-d',
            'attendances' => 'required|array',
            // Validate từng phần tử trong mảng attendances
            'attendances.*.student_id' => 'required|exists:users,id', // Đảm bảo student_id tồn tại trong bảng users
            'attendances.*.status' => ['required', Rule::in(['present', 'absent', 'late'])], // Chỉ chấp nhận 3 trạng thái này
        ]);

        $scheduleId = $validated['schedule_id'];
        $date = $validated['date'];
        $attendanceData = $validated['attendances'];

        // Sử dụng transaction để đảm bảo toàn vẹn dữ liệu
        DB::beginTransaction();
        try {
            foreach ($attendanceData as $att) {
                // updateOrCreate:
                // - Tìm bản ghi dựa trên schedule_id, student_id, date
                // - Nếu tìm thấy: Cập nhật status
                // - Nếu không tìm thấy: Tạo bản ghi mới với tất cả thông tin
                Attendance::updateOrCreate(
                    [
                        'schedule_id' => $scheduleId,
                        'student_id' => $att['student_id'],
                        'date' => $date, // Thêm date vào điều kiện tìm kiếm/tạo mới
                    ],
                    [
                        'status' => $att['status'],
                        // Thêm 'note' nếu ứng dụng Flutter của bạn gửi nó lên
                        // 'note' => $att['note'] ?? null,
                    ]
                );
            }
            DB::commit(); // Hoàn tất transaction nếu không có lỗi
            return response()->json(['message' => 'Lưu điểm danh thành công!'], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Hoàn tác transaction nếu có lỗi
            // Ghi lại lỗi để debug
            \Log::error("Lỗi Lưu Điểm Danh Hàng Loạt: " . $e->getMessage());
            return response()->json(['message' => 'Đã xảy ra lỗi khi lưu điểm danh.', 'error' => $e->getMessage()], 500);
        }
    }
}
