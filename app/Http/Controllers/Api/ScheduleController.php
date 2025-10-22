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
}
