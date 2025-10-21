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
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getHomeSummary(User $user)
    {
        // 1. Lấy ngày giờ hiện tại
        $today = Carbon::today();
        $now = Carbon::now();

        // 2. Lấy danh sách lịch dạy hôm nay (SỬA LẠI TÊN CỘT)
        $todaySchedulesQuery = Schedule::where('date', $today) // 👈 SỬA: 'teaching_date' -> 'date'
            ->whereHas('classCourseAssignment', function($query) use ($user) {
                // 👈 SỬA: 'user_id' -> 'teacher_id'
                $query->where('teacher_id', $user->id); 
            })
            ->with([
                // 👈 SỬA: Không có 'room.building'
                'room', 
                'classCourseAssignment.course',
                // Giả sử Model 'ClassCourseAssignment' có hàm 'classModel' trỏ đến bảng 'classes'
                'classCourseAssignment.classModel' 
            ])
            // 👈 SỬA: Không có 'lesson_start', sắp xếp theo 'session'
            ->orderBy('session', 'asc'); 

        $schedules = $todaySchedulesQuery->get();

        // 3. Tính toán thông tin Summary
        
        // 3.1. Tổng số tiết hôm nay 
        // ⚠️ LƯU Ý: Không thể tính chính xác. Tạm thời đếm số lượng buổi học.
        // Bạn nên sửa DB, tách 'session' thành 'lesson_start', 'lesson_end'
        $todayLessonsCount = $schedules->count(); 

        // 3.2. Tổng số tiết tuần này
        $startOfWeek = $today->copy()->startOfWeek();
        $endOfWeek = $today->copy()->endOfWeek();
        
        $weekLessonsCount = Schedule::whereBetween('date', [$startOfWeek, $endOfWeek]) // 👈 SỬA: 'teaching_date' -> 'date'
             ->whereHas('classCourseAssignment', function($query) use ($user) {
                $query->where('teacher_id', $user->id); // 👈 SỬA: 'user_id' -> 'teacher_id'
             })
             ->count(); // 👈 SỬA: Tạm thời đếm số lượng
        
        // 3.3. Phần trăm hoàn thành (ví dụ)
        $completionPercent = 0.0; 

        // 4. Định dạng lại danh sách lịch dạy (SỬA LẠI TÊN CỘT)
        $formattedSchedules = $schedules->map(function($schedule) use ($now) {
            
            // 👈 SỬA: Không có 'building_name'. Dùng 'location' từ bảng 'rooms'
            $location = $schedule->room?->location ?? 'N/A';
            // 👈 SỬA: 'room_name' -> 'name'
            $roomName = $schedule->room?->name ?? 'N/A';
            
            $courseName = $schedule->classCourseAssignment?->course?->name ?? 'N/A';
            
            // 👈 SỬA: Không có 'class_code'. Dùng 'name' từ bảng 'classes'
            $classCode = $schedule->classCourseAssignment?->classModel?->name ?? 'N/A';

            // ⚠️ LƯU Ý: Không thể tính status 'Đang diễn ra' vì không có time_start/time_end.
            // Lấy trực tiếp status từ DB ('scheduled', 'taught', ...)
            $status = $schedule->status;

            return [
                'id' => $schedule->id,
                // 👈 SỬA: 'time_range' và 'lessons' sẽ dùng chung cột 'session'
                'time_range' => $schedule->session, 
                'lessons' => $schedule->session,
                'title' => $courseName,
                'course_code' => "({$classCode})", 
                // 👈 SỬA: Kết hợp room name và location
                'location' => "{$roomName} - {$location}",
                'status' => $status,
            ];
        });

        // 5. Trả về JSON theo đúng cấu trúc HomeSummary.dart
        return response()->json([
            'summary' => [
                'today_lessons' => $todayLessonsCount,
                'week_lessons' => $weekLessonsCount,
                'completion_percent' => $completionPercent,
            ],
            'today_schedules' => $formattedSchedules,
        ]);
    }
}
