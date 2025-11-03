<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule; // Import model Schedule
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator; // Thêm trình xác thực

class ScheduleController extends Controller
{
    /**
     * Lấy danh sách Lịch học (ĐÃ SỬA: Dùng Paginate, đổi key)
     */
    public function index()
    {
        // 1. Tải lịch học CÙNG VỚI các quan hệ và phân trang
        $schedules = Schedule::with([
            'room', // Tải thông tin phòng
            'classCourseAssignment.teacher',  // Tải GV
            'classCourseAssignment.classModel', // Tải Lớp
            'classCourseAssignment.course'    // Tải Học phần
        ])
        ->orderBy('created_at', 'desc') // Sắp xếp mới nhất lên đầu
        
        // <<< SỬA 1: Dùng paginate(10) thay vì get() >>>
        // Điều này đảm bảo API trả về {'data': [...]}
        // giống hệt như UserController@index
        ->paginate(10); 

        // 2. Ánh xạ (map) dữ liệu sang định dạng Flutter mong muốn
        // (Lưu ý: paginate tự động map 'data', chúng ta cần biến đổi nó)
        $schedules->getCollection()->transform(function ($schedule) {
            
            $assignment = $schedule->classCourseAssignment;
            $teacherName = $assignment?->teacher?->name ?? 'N/A';
            $classCode   = $assignment?->classModel?->name ?? 'N/A';
            $courseName  = $assignment?->course?->name ?? 'N/A';
            $semester    = $assignment?->semester ?? 'N/A'; 
            $roomName    = $schedule->room?->name ?? 'N/A';

            // <<< SỬA 2: Đổi key thành snake_case (vd: teacher_name) cho nhất quán >>>
            return [
                'id' => $schedule->id, // Quan trọng cho Sửa/Xóa
                
                // Các key Flutter mong đợi cho Bảng (DataTable)
                'teacher_name' => $teacherName,
                'class_code'   => $classCode,
                'course_name'  => $courseName,
                'semester'     => $semester,
                'room_name'    => $roomName,

                // Gửi thêm ID + Dữ liệu gốc để Form 'Sửa' có thể chọn giá trị mặc định
                'room_id' => $schedule->room_id,
                'class_course_assignment_id' => $schedule->class_course_assignment_id,
                'date' => $schedule->date->toDateString(), // Gửi ngày (Y-m-d)
                'session' => $schedule->session,          // Gửi ca học
            ];
        });

        return response()->json($schedules); // Trả về đối tượng Paginator
    }

    /**
     * Tạo mới lịch học (ĐÃ SỬA)
     */
    public function store(Request $request)
    {
        // 1. Validate các ID mà Flutter gửi lên
        $validator = Validator::make($request->all(), [
            'class_course_assignment_id' => 'required|exists:class_course_assignments,id',
            'room_id' => 'required|exists:rooms,id',
            'date' => 'required|date_format:Y-m-d', // Flutter phải gửi Y-m-d
            'session' => 'required|string|max:255',
            'status' => 'nullable|string', 
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        // 2. Tạo bản ghi
        try {
            $dataToCreate = $validator->validated();
            if(empty($dataToCreate['status'])) {
                $dataToCreate['status'] = 'scheduled'; // Gán giá trị mặc định
            }
            
            $schedule = Schedule::create($dataToCreate);
            
            // Trả về rỗng (vì api_service.dart của bạn là Future<void>)
            return response()->json(null, 201); // Created
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi máy chủ khi tạo: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Lấy 1 lịch học
     */
    public function show(string $id)
    {
        $schedule = Schedule::with(['room', 'classCourseAssignment.teacher'])->find($id);
        if (!$schedule) {
            return response()->json(['message' => 'Không tìm thấy lịch học'], 404);
        }
        // (Lưu ý: Bạn có thể cần map dữ liệu ở đây nếu Flutter cần)
        return response()->json($schedule);
    }

    /**
     * Cập nhật lịch học (ĐÃ SỬA)
     */
    public function update(Request $request, string $id)
    {
        // 1. Tìm bản ghi
        $schedule = Schedule::find($id);
        if (!$schedule) {
            return response()->json(['message' => 'Không tìm thấy lịch học'], 404);
        }

        // 2. Validate
        $validator = Validator::make($request->all(), [
            'class_course_assignment_id' => 'required|exists:class_course_assignments,id',
            'room_id' => 'required|exists:rooms,id',
            'date' => 'required|date_format:Y-m-d',
            'session' => 'required|string|max:255',
            'status' => 'nullable|string', 
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }
        
        // 3. Cập nhật
        try {
            $schedule->update($validator->validated());
            
            // Trả về rỗng (vì api_service.dart của bạn là Future<void>)
            return response()->json(null, 200); // OK
            
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi máy chủ khi cập nhật: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Xóa lịch học
     */
    public function destroy(string $id)
    {
        $schedule = Schedule::find($id);
        if (!$schedule) {
            return response()->json(['message' => 'Không tìm thấy lịch học'], 404);
        }
        $schedule->delete();
        
        // <<< SỬA 3: Trả về 204 (No Content) thay vì 200 >>>
        // Đây là chuẩn RESTful cho việc xóa thành công
        // api_service.dart (hàm deleteUser) cũng đang check 204
        return response()->json(null, 204); 
    }

    // --- CÁC HÀM API KHÁC (Giữ nguyên) ---
    public function getSchedulesByDateForTeacher(Request $request, User $user)
    {
         $request->validate(['date' => 'required|date_format:Y-m-d']);
         $date = Carbon::parse($request->query('date'));
         $schedules = Schedule::where('date', $date)
             ->whereHas('classCourseAssignment', function ($q) use ($user) {
                 $q->where('teacher_id', $user->id);
             })
             ->with(['classCourseAssignment.course', 'classCourseAssignment.classModel'])
             ->orderBy('session', 'asc')
             ->get();
         $formatted = $schedules->map(function ($schedule) {
             $courseName = $schedule->classCourseAssignment?->course?->name ?? 'N/A';
             $classCode = $schedule->classCourseAssignment?->classModel?->name ?? 'N/A';
             return [
                 'schedule_id' => $schedule->id,
                 'display_name' => "{$courseName} ({$classCode}) - {$schedule->session}"
             ];
         });
         return response()->json($formatted);
    }

    public function getAvailableSchedulesForLeave(User $user)
    {
         $upcomingSchedules = Schedule::where('date', '>=', Carbon::tomorrow())
             ->where('status', 'scheduled')
             ->whereHas('classCourseAssignment', function ($q) use ($user) {
                 $q->where('teacher_id', $user->id);
             })
             ->whereDoesntHave('leaveRequests', function ($query) {
                 $query->whereIn('status', ['pending', 'approved']);
             })
             ->with(['room', 'classCourseAssignment.course', 'classCourseAssignment.classModel'])
             ->orderBy('date', 'asc')
             ->orderBy('session', 'asc')
             ->limit(50)
             ->get();
         $formatted = $upcomingSchedules->map(function ($schedule) {
             $courseName = $schedule->classCourseAssignment?->course?->name ?? 'N/A';
             $classCode = $schedule->classCourseAssignment?->classModel?->name ?? 'N/A';
             return [
                 'schedule_id' => $schedule->id,
                 'display_name' => $schedule->date->format('d/m/Y') . " - {$schedule->session} - {$courseName} ({$classCode})"
             ];
         });
         return response()->json($formatted);
    }
}