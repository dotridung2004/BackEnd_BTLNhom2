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
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     * --- ĐÃ SỬA LỖI ---
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
}