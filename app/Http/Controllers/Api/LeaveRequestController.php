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
     * @OA\Get(
     * path="/api/leaverequests",
     * operationId="getLeaveRequestsList",
     * tags={"Leave Requests"},
     * summary="Lấy DS Đơn nghỉ (Chưa triển khai)",
     * security={{"bearerAuth":{}}},
     * @OA\Response(response=200, description="Chưa triển khai")
     * )
     */
    public function index()
    {
        // (Chưa cần triển khai)
    }

    /**
     * @OA\Post(
     * path="/api/leave-requests",
     * operationId="storeLeaveRequest",
     * tags={"Leave Requests"},
     * summary="Gửi yêu cầu xin nghỉ (Dùng route /api/leave-requests)",
     * description="Route /api/leaverequests (resource) cũng trỏ về đây",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"schedule_id", "reason"},
     * @OA\Property(property="schedule_id", type="integer", description="ID của lịch dạy muốn nghỉ", example=12),
     * @OA\Property(property="reason", type="string", description="Lý do xin nghỉ", example="Bị ốm")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Gửi yêu cầu thành công",
     * @OA\JsonContent()
     * ),
     * @OA\Response(
     * response=401,
     * description="Chưa đăng nhập"
     * ),
     * @OA\Response(
     * response=422,
     * description="Lỗi validation"
     * )
     * )
     *
     * @OA\Post(
     * path="/api/leaverequests",
     * operationId="storeLeaveRequestResource",
     * tags={"Leave Requests"},
     * summary="Gửi yêu cầu xin nghỉ (Dùng route resource /api/leaverequests)",
     * description="Route /api/leave-requests (custom) cũng trỏ về đây",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"schedule_id", "reason"},
     * @OA\Property(property="schedule_id", type="integer", description="ID của lịch dạy muốn nghỉ", example=12),
     * @OA\Property(property="reason", type="string", description="Lý do xin nghỉ", example="Bị ốm")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Gửi yêu cầu thành công",
     * @OA\JsonContent()
     * ),
     * @OA\Response(
     * response=401,
     * description="Chưa đăng nhập"
     * ),
     * @OA\Response(
     * response=422,
     * description="Lỗi validation"
     * )
     * )
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
        //     $documentUrl = asset('storage/'Gửi yêu cầu dạy bù thành công . $path);
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
     * @OA\Get(
     * path="/api/leaverequests/{leaverequest}",
     * operationId="getLeaveRequestById",
     * tags={"Leave Requests"},
     * summary="Xem 1 Đơn nghỉ (Chưa triển khai)",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="leaverequest", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Chưa triển khai")
     * )
     */
    public function show(string $id)
    {
        // (Chưa cần triển khai)
    }

    /**
     * @OA\Put(
     * path="/api/leaverequests/{leaverequest}",
     * operationId="updateLeaveRequest",
     * tags={"Leave Requests"},
     * summary="Cập nhật Đơn nghỉ (Chưa triển khai)",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="leaverequest", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Chưa triển khai")
     * )
     */
    public function update(Request $request, string $id)
    {
        // (Chưa cần triển khai)
    }

    /**
     * @OA\Delete(
     * path="/api/leaverequests/{leaverequest}",
     * operationId="deleteLeaveRequest",
     * tags={"Leave Requests"},
     * summary="Xóa Đơn nghỉ (Chưa triển khai)",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="leaverequest", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Chưa triển khai")
     * )
     */
    public function destroy(string $id)
    {
        // (Chưa cần triển khai)
    }

    /**
     * @OA\Get(
     * path="/api/users/{user}/leave-history",
     * operationId="getLeaveHistoryForTeacher",
     * tags={"Leave Requests"},
     * summary="Lấy lịch sử xin nghỉ của giáo viên",
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
     * @OA\Property(property="leave_request_id", type="integer"),
     * @OA\Property(property="subject_name", type="string"),
     * @OA\Property(property="leave_status", type="string", enum={"pending", "approved", "rejected"}),
     * @OA\Property(property="reason", type="string")
     * )
     * )
     * )
     * )
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