<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// 👇 *** THÊM CÁC DÒNG NÀY ***
use App\Models\Schedule;
use App\Models\MakeupClass;
use Illuminate\Support\Facades\Auth; // Để lấy user đã đăng nhập
use Illuminate\Validation\Rule; // (Có thể cần nếu validate phức tạp hơn)

class MakeupClassController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/makeupclasses",
     * operationId="getMakeupClassesList",
     * tags={"Makeup Classes"},
     * summary="Lấy DS Lớp dạy bù (Chưa triển khai)",
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
     * path="/api/makeup-classes",
     * operationId="storeMakeupClass",
     * tags={"Makeup Classes"},
     * summary="Gửi yêu cầu dạy bù (Dùng route /api/makeup-classes)",
     * description="Route /api/makeupclasses (resource) cũng trỏ về đây",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"original_schedule_id", "new_schedule_date", "new_session", "new_room_id"},
     * @OA\Property(property="original_schedule_id", type="integer", description="ID lịch dạy GỐC (buổi nghỉ)", example=12),
     * @OA\Property(property="new_schedule_date", type="string", format="date", description="Ngày dạy bù (Y-m-d)", example="2025-10-30"),
     * @OA\Property(property="new_session", type="string", description="Ca/tiết dạy bù", example="3-4"),
     * @OA\Property(property="new_room_id", type="integer", description="ID của phòng dạy bù", example=5)
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Gửi yêu cầu dạy bù thành công!",
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
     * path="/api/makeupclasses",
     * operationId="storeMakeupClassResource",
     * tags={"Makeup Classes"},
     * summary="Gửi yêu cầu dạy bù (Dùng route resource /api/makeupclasses)",
     * description="Route /api/makeup-classes (custom) cũng trỏ về đây",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"original_schedule_id", "new_schedule_date", "new_session", "new_room_id"},
     * @OA\Property(property="original_schedule_id", type="integer", description="ID lịch dạy GỐC (buổi nghỉ)", example=12),
     * @OA\Property(property="new_schedule_date", type="string", format="date", description="Ngày dạy bù (Y-m-d)", example="2025-10-30"),
     * @OA\Property(property="new_session", type="string", description="Ca/tiết dạy bù", example="3-4"),
     * @OA\Property(property="new_room_id", type="integer", description="ID của phòng dạy bù", example=5)
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Gửi yêu cầu dạy bù thành công!",
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
        // 1. Validate dữ liệu
        $validated = $request->validate([
            // 'teacher_id' SẼ ĐƯỢC LẤY TỪ AUTH::ID() NÊN KHÔNG CẦN VALIDATE TỪ BODY
            'original_schedule_id' => 'required|exists:schedules,id',
            'new_schedule_date' => 'required|date_format:Y-m-d', // Ngày bù
            'new_session' => 'required|string', // Ca/tiết bù
            'new_room_id' => 'required|exists:rooms,id', // Phòng bù
            'note' => 'nullable|string|max:500', // Ghi chú thêm
        ]);

        // Lấy teacher_id từ người dùng đã xác thực (AN TOÀN HƠN)
        $teacherId = Auth::id();
        if (!$teacherId) {
            return response()->json(['message' => 'Lỗi xác thực người dùng.'], 401);
        }

        // --- Logic tạo lịch dạy mới (bản nháp) ---
        // 2. Lấy thông tin từ lịch dạy gốc
        $originalSchedule = Schedule::findOrFail($validated['original_schedule_id']);
        $assignmentId = $originalSchedule->class_course_assignment_id;

        // 3. Tạo một bản ghi Schedule mới cho buổi dạy bù
        $newSchedule = Schedule::create([
            'class_course_assignment_id' => $assignmentId,
            'room_id' => $validated['new_room_id'],
            'date' => $validated['new_schedule_date'],
            'session' => $validated['new_session'],
            'topic' => 'Dạy bù cho ngày ' . $originalSchedule->date->format('d/m/Y'), // Ví dụ topic
            'status' => 'makeup', // Đánh dấu là lịch dạy bù (hoặc pending_makeup)
        ]);

        // 4. Tạo bản ghi MakeupClass để liên kết
        $makeupClass = MakeupClass::create([
            'teacher_id' => $teacherId, // 👈 *** SỬA: Dùng $teacherId từ Auth ***
            'original_schedule_id' => $validated['original_schedule_id'],
            'new_schedule_id' => $newSchedule->id, // Liên kết đến lịch bù vừa tạo
            'status' => 'pending', // Trạng thái chờ duyệt
            // 'note' => $validated['note'] // Bạn có thể lưu note ở đây nếu bảng makeup_classes có cột 'note'
        ]);

        // 5. (Tùy chọn) Cập nhật trạng thái lịch dạy gốc
        // $originalSchedule->update(['status' => 'cancelled']); // Cân nhắc kỹ

        // 6. Trả về thành công
        return response()->json(['message' => 'Gửi yêu cầu dạy bù thành công!', 'data' => $makeupClass], 201);
    }

    /**
     * @OA\Get(
     * path="/api/makeupclasses/{makeupclass}",
     * operationId="getMakeupClassById",
     * tags={"Makeup Classes"},
     * summary="Xem 1 Lớp bù (Chưa triển khai)",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="makeupclass", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Chưa triển khai")
     * )
     */
    public function show(string $id)
    {
        //
    }

    /**
     * @OA\Put(
     * path="/api/makeupclasses/{makeupclass}",
     * operationId="updateMakeupClass",
     * tags={"Makeup Classes"},
     * summary="Cập nhật Lớp bù (Chưa triển khai)",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="makeupclass", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Chưa triển khai")
     * )
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * @OA\Delete(
     * path="/api/makeupclasses/{makeupclass}",
     * operationId="deleteMakeupClass",
     * tags={"Makeup Classes"},
     * summary="Xóa Lớp bù (Chưa triển khai)",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="makeupclass", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Chưa triển khai")
     * )
     */
    public function destroy(string $id)
    {
        //
    }
}