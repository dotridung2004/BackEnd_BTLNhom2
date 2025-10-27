<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 👇 *** THÊM CÁC DÒNG NÀY ***
use App\Models\Schedule; // Import model Schedule
use App\Models\User;     // (Giữ lại nếu các hàm khác cần)
use Carbon\Carbon;       // (Giữ lại nếu các hàm khác cần)
// 👆 *** KẾT THÚC THÊM ***

class ScheduleController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/schedules",
     * operationId="getSchedulesList",
     * tags={"Schedules (CRUD)"},
     * summary="Lấy danh sách Lịch học", // Sửa summary
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Thành công, trả về danh sách lịch học", // Sửa description
     * @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Schedule")) // Tham chiếu đến Schema (nếu có)
     * ),
     * @OA\Response(response=401, description="Chưa xác thực")
     * )
     */
    public function index()
    {
        // --- 👇 BẮT ĐẦU TRIỂN KHAI ---
        // Lấy tất cả lịch học và load các quan hệ cần thiết cho Frontend
        // Dựa vào model Schedule.fromJson của bạn, chúng ta cần:
        // - room
        // - classCourseAssignment.teacher
        // - classCourseAssignment.classModel
        // - classCourseAssignment.course
        $schedules = Schedule::with([
            'room', // Tải thông tin phòng học
            'classCourseAssignment.teacher',  // Tải thông tin giảng viên qua bảng trung gian
            'classCourseAssignment.classModel', // Tải thông tin lớp học qua bảng trung gian
            'classCourseAssignment.course'    // Tải thông tin học phần qua bảng trung gian
        ])
        ->orderBy('date', 'asc') // Sắp xếp theo ngày (tùy chọn)
        ->orderBy('session', 'asc') // Sắp xếp theo tiết (tùy chọn)
        ->get(); // Lấy tất cả (Cân nhắc dùng ->paginate(50) nếu dữ liệu lớn)

        // Trả về dữ liệu dưới dạng JSON
        return response()->json($schedules);
        // --- 👆 KẾT THÚC TRIỂN KHAI ---
    }

    /**
     * @OA\Post(...) // Các hàm khác giữ nguyên (chưa triển khai)
     */
    public function store(Request $request)
    {
        // ... (Chưa triển khai)
    }

    /**
     * @OA\Get(...) // Các hàm khác giữ nguyên (chưa triển khai)
     */
    public function show(string $id)
    {
        // ... (Chưa triển khai)
    }

    /**
     * @OA\Put(...) // Các hàm khác giữ nguyên (chưa triển khai)
     */
    public function update(Request $request, string $id)
    {
        // ... (Chưa triển khai)
    }

    /**
     * @OA\Delete(...) // Các hàm khác giữ nguyên (chưa triển khai)
     */
    public function destroy(string $id)
    {
        // ... (Chưa triển khai)
    }

    // --- CÁC HÀM API KHÁC (getSchedulesByDateForTeacher, getAvailableSchedulesForLeave) ---
    // Giữ nguyên các hàm này nếu chúng đã hoạt động đúng
    // ...
    public function getSchedulesByDateForTeacher(Request $request, User $user)
    {
        // ... (Giữ nguyên code hiện tại của bạn)
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
        // ... (Giữ nguyên code hiện tại của bạn)
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