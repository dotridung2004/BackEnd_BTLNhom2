<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/attendances",
     * operationId="getAttendancesList",
     * tags={"Attendance"},
     * summary="Lấy DS Điểm danh (Chưa triển khai)",
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Chưa triển khai")
     * )
     */
    public function index()
    {
        // ...
    }

    /**
     * @OA\Post(
     * path="/api/attendances",
     * operationId="storeAttendance",
     * tags={"Attendance"},
     * summary="Tạo Điểm danh (Chưa triển khai)",
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Chưa triển khai")
     * )
     */
    public function store(Request $request)
    {
        // ...
    }

    /**
     * @OA\Get(
     * path="/api/attendances/{attendance}",
     * operationId="getAttendanceById",
     * tags={"Attendance"},
     * summary="Lấy 1 Điểm danh (Chưa triển khai)",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="attendance", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Chưa triển khai")
     * )
     */
    public function show($id)
    {
        // ...
    }

    /**
     * @OA\Put(
     * path="/api/attendances/{attendance}",
     * operationId="updateAttendance",
     * tags={"Attendance"},
     * summary="Cập nhật Điểm danh (Chưa triển khai)",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="attendance", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Chưa triển khai")
     * )
     */
    public function update(Request $request, $id)
    {
        // ...
    }

    /**
     * @OA\Delete(
     * path="/api/attendances/{attendance}",
     * operationId="deleteAttendance",
     * tags={"Attendance"},
     * summary="Xóa Điểm danh (Chưa triển khai)",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="attendance", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Chưa triển khai")
     * )
     */
    public function destroy($id)
    {
        // ...
    }

    /**
     * @OA\Get(
     * path="/api/schedules/{schedule}/students-attendance",
     * operationId="getStudentsAndAttendanceForSchedule",
     * tags={"Attendance"},
     * summary="Lấy danh sách SV và trạng thái điểm danh của 1 lịch học",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="schedule",
     * in="path",
     * required=true,
     * description="ID của lịch học (schedule)",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công. Trả về mảng danh sách SV và status",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(
     * @OA\Property(property="student_id", type="integer"),
     * @OA\Property(property="student_name", type="string"),
     * @OA\Property(property="status", type="string", enum={"present", "absent", "late"})
     * )
     * )
     * )
     * )
     */
    public function getStudentsAndAttendance(Request $request, Schedule $schedule)
    {
        // 1. Lấy danh sách sinh viên
        $students = $schedule->classCourseAssignment
            ->classModel
            ?->students()
            ->orderBy('name', 'asc')
            ->get(['users.id', 'users.name']);

        if (!$students || $students->isEmpty()) {
            return response()->json([]); // Trả về mảng rỗng nếu không có sinh viên
        }

        // 2. Lấy điểm danh đã có (Đã xóa where('date'))
        $existingAttendance = Attendance::where('schedule_id', $schedule->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->pluck('status', 'student_id');

        // 3. Kết hợp kết quả
        $results = $students->map(function ($student) use ($existingAttendance) {
            return [
                'student_id'   => $student->id,
                'student_name' => $student->name,
                'status'       => $existingAttendance->get($student->id, 'present') // Mặc định là 'present'
            ];
        });

        return response()->json($results);
    }

    /**
     * @OA\Post(
     * path="/api/attendances/bulk-save",
     * operationId="saveBulkAttendance",
     * tags={"Attendance"},
     * summary="Lưu điểm danh hàng loạt cho một lịch học",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"schedule_id", "attendances"},
     * @OA\Property(property="schedule_id", type="integer", description="ID của lịch học", example=15),
     * @OA\Property(
     * property="attendances",
     * type="array",
     * @OA\Items(
     * type="object",
     * required={"student_id", "status"},
     * @OA\Property(property="student_id", type="integer", example=101),
     * @OA\Property(property="status", type="string", enum={"present", "absent", "late"}, example="present")
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Lưu điểm danh thành công"
     * ),
     * @OA\Response(
     * response=422,
     * description="Dữ liệu không hợp lệ"
     * ),
     * @OA\Response(
     * response=500,
     * description="Lỗi server khi lưu"
     * )
     * )
     */
    public function saveBulkAttendance(Request $request)
    {
        // 1. Validate (Đã xóa 'date')
        $validated = $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:users,id',
            'attendances.*.status' => ['required', Rule::in(['present', 'absent', 'late'])],
        ]);

        $scheduleId = $validated['schedule_id'];
        $attendanceData = $validated['attendances'];

        // 2. Lưu vào DB (Đã xóa 'date')
        DB::beginTransaction();
        try {
            foreach ($attendanceData as $att) {
                Attendance::updateOrCreate(
                    [
                        'schedule_id' => $scheduleId,
                        'student_id'  => $att['student_id'],
                        // 'date' => $date, // <--- ĐÃ XÓA
                    ],
                    [
                        'status' => $att['status'],
                    ]
                );
            }
            DB::commit();
            return response()->json(['message' => 'Lưu điểm danh thành công!'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Lỗi Lưu Điểm Danh Hàng Loạt: " . $e->getMessage());
            return response()->json(['message' => 'Đã xảy ra lỗi khi lưu điểm danh.', 'error' => $e->getMessage()], 500);
        }
    }

    // --- HÀM saveBulkAttendance BỊ TRÙNG LẶP ĐÃ BỊ XÓA KHỎI ĐÂY ---
    
}