<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // 👈 *** THÊM DÒNG NÀY ***

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // (Chưa cần triển khai)
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'reason' => 'required|string|max:1000',
            // 'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        $teacherId = Auth::id(); // Lấy ID từ token
        if (!$teacherId) {
             return response()->json(['message' => 'Lỗi xác thực người dùng.'], 401);
        }

        $documentUrl = null;
        // (Xử lý upload file nếu có)

        $leaveRequest = LeaveRequest::create([
            'teacher_id' => $teacherId,
            'schedule_id' => $validated['schedule_id'],
            'reason' => $validated['reason'],
            'document_url' => $documentUrl,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Gửi yêu cầu nghỉ thành công!', 'data' => $leaveRequest], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // (Chưa cần triển khai)
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // (Chưa cần triển khai)
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // (Chưa cần triển khai)
    }

    /**
     * Lấy lịch sử các đơn xin nghỉ của một giáo viên.
     */
    public function getLeaveHistoryForTeacher(User $user)
    {
        // (Sửa lỗi bảo mật: Dùng Auth::id() thay vì $user->id)
        $teacherId = Auth::id() ?? $user->id;

        $history = LeaveRequest::where('teacher_id', $teacherId) // 👈 Sửa
            ->with(['schedule.room', 'schedule.classCourseAssignment.course', 'schedule.classCourseAssignment.classModel'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $formatted = $history->map(function ($leaveRequest) {
            /** @var \App\Models\Schedule|null $schedule */
            $schedule = $leaveRequest->schedule;
            if (!$schedule) {
                return null;
            }

            $location = $schedule->room?->location ?? 'N/A';
            $roomName = $schedule->room?->name ?? 'N/A';
            $courseName = $schedule->classCourseAssignment?->course?->name ?? 'N/A';
            $classCode = $schedule->classCourseAssignment?->classModel?->name ?? 'N/A';

            return [
                'leave_request_id' => $leaveRequest->id,
                'schedule_id' => $schedule->id,
                'date_string' => $schedule->date->format('d/m/Y'),
                'time_range' => $schedule->session,
                'lesson_period' => $schedule->session,
                'subject_name' => $courseName,
                'course_code' => "({$classCode})",
                'location' => "{$roomName} - {$location}",
                'leave_status' => $leaveRequest->status,
                'reason' => $leaveRequest->reason,
            ];
        })->whereNotNull();

        return response()->json($formatted->values());
    }
}