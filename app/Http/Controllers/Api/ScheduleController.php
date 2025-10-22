<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth; // 👈 *** THÊM DÒNG NÀY ***

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

    // --- API CHO ĐIỂM DANH (DROPDOWN) ---
    
    public function getSchedulesByDateForTeacher(Request $request, User $user)
    {
        $request->validate(['date' => 'required|date_format:Y-m-d']);
        $date = Carbon::parse($request->query('date'));
        
        // (Sửa lỗi bảo mật: Dùng Auth::id() thay vì $user->id)
        $teacherId = Auth::id() ?? $user->id;

        $schedules = Schedule::where('date', $date)
            ->whereHas('classCourseAssignment', function($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId); // 👈 Sửa
            })
            ->with(['classCourseAssignment.course', 'classCourseAssignment.classModel'])
            ->orderBy('session', 'asc')
            ->get();

        $formatted = $schedules->map(function($schedule) {
            $courseName = $schedule->classCourseAssignment?->course?->name ?? 'N/A';
            $classCode = $schedule->classCourseAssignment?->classModel?->name ?? 'N/A';
            return [
                'schedule_id' => $schedule->id,
                'display_name' => "{$courseName} ({$classCode}) - {$schedule->session}"
            ];
        });

        return response()->json($formatted);
    }
    
    // --- API CHO ĐĂNG KÝ NGHỈ (DROPDOWN) ---

    public function getAvailableSchedulesForLeave(User $user)
    {
        // (Sửa lỗi bảo mật: Dùng Auth::id() thay vì $user->id)
        $teacherId = Auth::id() ?? $user->id;

        $upcomingSchedules = Schedule::where('date', '>=', Carbon::tomorrow())
            ->where('status', 'scheduled')
            ->whereHas('classCourseAssignment', function($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId); // 👈 Sửa
            })
            ->whereDoesntHave('leaveRequests', function ($query) {
                $query->whereIn('status', ['pending', 'approved']);
            })
            ->with(['classCourseAssignment.course', 'classCourseAssignment.classModel'])
            ->orderBy('date', 'asc')
            ->orderBy('session', 'asc')
            ->limit(50)
            ->get();

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