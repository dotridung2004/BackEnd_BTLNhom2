<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // 👈 Thêm
use App\Models\Schedule; // 👈 Thêm
use Carbon\Carbon; //

class ScheduleController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/schedules",
     * operationId="getSchedulesList",
     * tags={"Schedules (CRUD)"},
     * summary="Lấy danh sách Lịch học (Chưa triển khai)",
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Chưa triển khai")
     * )
     */
    public function index()
    {
        //
    }

    /**
     * @OA\Post(
     * path="/api/schedules",
     * operationId="storeSchedule",
     * tags={"Schedules (CRUD)"},
     * summary="Tạo Lịch học (Chưa triển khai)",
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Chưa triển khai")
     * )
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * @OA\Get(
     * path="/api/schedules/{schedule}",
     * operationId="getScheduleById",
     * tags={"Schedules (CRUD)"},
     * summary="Lấy 1 Lịch học (Chưa triển khai)",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="schedule", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Chưa triển khai")
     * )
     */
    public function show(string $id)
    {
        //
    }

    /**
     * @OA\Put(
     * path="/api/schedules/{schedule}",
     * operationId="updateSchedule",
     * tags={"Schedules (CRUD)"},
     * summary="Cập nhật Lịch học (Chưa triển khai)",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="schedule", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Chưa triển khai")
     * )
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * @OA\Delete(
     * path="/api/schedules/{schedule}",
     * operationId="deleteSchedule",
     * tags={"Schedules (CRUD)"},
     * summary="Xóa Lịch học (Chưa triển khai)",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="schedule", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Chưa triển khai")
     * )
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * @OA\Get(
     * path="/api/users/{user}/schedules-by-date",
     * operationId="getSchedulesByDateForTeacher",
     * tags={"Schedules"},
     * summary="Lấy lịch dạy (theo ngày) của giáo viên (cho dropdown xin nghỉ)",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="user",
     * in="path",
     * required=true,
     * description="ID của giáo viên",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Parameter(
     * name="date",
     * in="query",
     * required=true,
     * description="Ngày cần lấy lịch (Y-m-d)",
     * @OA\Schema(type="string", format="date", example="2025-10-24")
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(
     * @OA\Property(property="schedule_id", type="integer", example=1),
     * @OA\Property(property="display_name", type="string", example="Lập trình Web (IT1) - 1-2")
     * )
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Lỗi validate ngày"
     * )
     * )
     */
    public function getSchedulesByDateForTeacher(Request $request, User $user)
    {
        // Validate ngày gửi lên
        $request->validate(['date' => 'required|date_format:Y-m-d']);
        $date = Carbon::parse($request->query('date'));

        // Lấy lịch dạy của giáo viên trong ngày đó
        $schedules = Schedule::where('date', $date)
            ->whereHas('classCourseAssignment', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            })
            // Load các thông tin cần thiết để hiển thị tên
            ->with(['classCourseAssignment.course', 'classCourseAssignment.classModel'])
            ->orderBy('session', 'asc') // Sắp xếp theo tiết học
            ->get();

        // Format lại dữ liệu cho dropdown ở Flutter
        $formatted = $schedules->map(function ($schedule) {
            // Lấy tên môn học và tên lớp (giả sử cột 'name' trong bảng classes là mã lớp/tên lớp)
            $courseName = $schedule->classCourseAssignment?->course?->name ?? 'N/A';
            $classCode = $schedule->classCourseAssignment?->classModel?->name ?? 'N/A';
            return [
                'schedule_id' => $schedule->id, // ID của lịch dạy
                // Kết hợp thông tin để hiển thị (Tên môn (Mã lớp) - Tiết học)
                'display_name' => "{$courseName} ({$classCode}) - {$schedule->session}"
            ];
        });

        return response()->json($formatted);
    }

    /**
     * @OA\Get(
     * path="/api/users/{user}/available-schedules-for-leave",
     * operationId="getAvailableSchedulesForLeave",
     * tags={"Schedules"},
     * summary="Lấy lịch dạy SẮP TỚI của giáo viên (để chọn khi xin nghỉ)",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="user",
     * in="path",
     * required=true,
     * description="ID của giáo viên",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(
     * @OA\Property(property="schedule_id", type="integer", example=10),
     * @OA\Property(property="display_name", type="string", example="24/10/2025 - 1-2 - Lập trình Web (IT1)")
     * )
     * )
     * )
     * )
     */
    public function getAvailableSchedulesForLeave(User $user)
    {
        // Lấy lịch dạy sắp tới (ví dụ: từ ngày mai trở đi)
        // và chưa bị hủy hoặc chưa có đơn xin nghỉ pending/approved
        $upcomingSchedules = Schedule::where('date', '>=', Carbon::tomorrow())
            ->where('status', 'scheduled') // Chỉ lấy lịch chưa dạy/hủy
            ->whereHas('classCourseAssignment', function ($q) use ($user) {
                $q->where('teacher_id', $user->id);
            })
            // Loại trừ những lịch đã có đơn xin nghỉ đang chờ hoặc đã duyệt
            ->whereDoesntHave('leaveRequests', function ($query) {
                $query->whereIn('status', ['pending', 'approved']);
            })
            ->with(['room', 'classCourseAssignment.course', 'classCourseAssignment.classModel'])
            ->orderBy('date', 'asc')
            ->orderBy('session', 'asc')
            ->limit(50) // Giới hạn số lượng trả về
            ->get();

        // Format tương tự getSchedulesByDateForTeacher nhưng thêm ngày
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