<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 👇 *** THÊM CÁC DÒNG NÀY ***
use App\Models\User;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth; // Để lấy user đã đăng nhập (khuyến nghị)

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
     * --- HÀM NÀY ĐÃ ĐƯỢC BỔ SUNG ---
     */
    public function store(Request $request)
    {
        // 1. Validate dữ liệu gửi lên từ Flutter
        $validated = $request->validate([
            // 'teacher_id' => 'required|exists:users,id', // Sẽ an toàn hơn nếu lấy từ Auth::id()
            'schedule_id' => 'required|exists:schedules,id',
            'reason' => 'required|string|max:1000',
            // 'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048' // Ví dụ validate file upload
        ]);

        // (Tùy chọn) Xử lý upload file minh chứng nếu có
        $documentUrl = null;
        // if ($request->hasFile('document')) {
        //     $path = $request->file('document')->store('leave_documents', 'public');
        //     $documentUrl = asset('storage/' . $path);
        // }

        // 2. Tạo bản ghi mới trong database
        $leaveRequest = LeaveRequest::create([
            // Lấy teacher_id từ người dùng đã xác thực để bảo mật
            'teacher_id' => Auth::id(), // Giả sử bạn đã dùng middleware 'auth:sanctum' cho route này
            'schedule_id' => $validated['schedule_id'],
            'reason' => $validated['reason'],
            'document_url' => $documentUrl,
            'status' => 'pending', // Mặc định là 'chờ duyệt'
        ]);

        // 3. Trả về thông báo thành công
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
     * --- HÀM NÀY ĐÃ ĐƯỢC SỬA LẠI ---
     * Lấy lịch sử các đơn xin nghỉ của một giáo viên.
     */
    public function getLeaveHistoryForTeacher(User $user)
    {
        $history = LeaveRequest::where('teacher_id', $user->id)
            // Bạn có thể bỏ comment dòng dưới nếu chỉ muốn lấy đơn đã duyệt
            // ->where('status', 'approved')
            ->with(['schedule.room', 'schedule.classCourseAssignment.course', 'schedule.classCourseAssignment.classModel'])
            ->orderBy('created_at', 'desc') // Sắp xếp theo ngày tạo đơn gần nhất
            ->limit(50) // Giới hạn 50 kết quả
            ->get();

        // Format lại dữ liệu cho giống với frontend
        $formatted = $history->map(function ($leaveRequest) {
            $schedule = $leaveRequest->schedule;
            // Nếu vì lý do nào đó lịch dạy gốc đã bị xóa, bỏ qua
            if (!$schedule) {
                return null;
            }

            // 👇 *** LOGIC BỊ THIẾU TRƯỚC ĐÂY ***
            $location = $schedule->room?->location ?? 'N/A';
            $roomName = $schedule->room?->name ?? 'N/A';
            $courseName = $schedule->classCourseAssignment?->course?->name ?? 'N/A';
            $classCode = $schedule->classCourseAssignment?->classModel?->name ?? 'N/A';
            // 👆 *** KẾT THÚC LOGIC BỊ THIẾU ***

            return [
                'leave_request_id' => $leaveRequest->id,
                'schedule_id' => $schedule->id,
                'date_string' => $schedule->date->format('d/m/Y'),
                'time_range' => $schedule->session,
                'lesson_period' => $schedule->session,
                'subject_name' => $courseName,
                'course_code' => "({$classCode})",
                'location' => "{$roomName} - {$location}",
                'leave_status' => $leaveRequest->status, // Thêm trạng thái đơn nghỉ
                'reason' => $leaveRequest->reason,       // Thêm lý do
            ];
        })->whereNotNull(); // Lọc bỏ các kết quả null

        return response()->json($formatted->values());
    }
}