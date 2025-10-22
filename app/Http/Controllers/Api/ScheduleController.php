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
    public function getSchedulesByDateForTeacher(Request $request, User $user)
    {
        // Validate ngày gửi lên
        $request->validate(['date' => 'required|date_format:Y-m-d']);
        $date = Carbon::parse($request->query('date'));

        // Lấy lịch dạy của giáo viên trong ngày đó
        $schedules = Schedule::where('date', $date)
            ->whereHas('classCourseAssignment', function($q) use ($user) {
                $q->where('teacher_id', $user->id);
            })
            // Load các thông tin cần thiết để hiển thị tên
            ->with(['classCourseAssignment.course', 'classCourseAssignment.classModel'])
            ->orderBy('session', 'asc') // Sắp xếp theo tiết học
            ->get();

        // Format lại dữ liệu cho dropdown ở Flutter
        $formatted = $schedules->map(function($schedule) {
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
    public function getAvailableSchedulesForLeave(User $user)
    {
        // Lấy lịch dạy sắp tới (ví dụ: từ ngày mai trở đi)
        // và chưa bị hủy hoặc chưa có đơn xin nghỉ pending/approved
        $upcomingSchedules = Schedule::where('date', '>=', Carbon::tomorrow())
            ->where('status', 'scheduled') // Chỉ lấy lịch chưa dạy/hủy
            ->whereHas('classCourseAssignment', function($q) use ($user) {
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
        $formatted = $upcomingSchedules->map(function($schedule) {
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
