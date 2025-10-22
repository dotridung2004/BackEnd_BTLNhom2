<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\MakeupClass;
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
     */
    // Ghi đè hoặc tạo hàm store
    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id', // Nên lấy từ user đã xác thực
            'original_schedule_id' => 'required|exists:schedules,id',
            'new_schedule_date' => 'required|date_format:Y-m-d', // Ngày bù
            'new_session' => 'required|string', // Ca/tiết bù
            'new_room_id' => 'required|exists:rooms,id', // Phòng bù
            'note' => 'nullable|string|max:500', // Ghi chú thêm
        ]);

        // --- Logic tạo lịch dạy mới (bản nháp) ---
        // Lấy thông tin từ lịch dạy gốc
        $originalSchedule = Schedule::findOrFail($validated['original_schedule_id']);
        $assignmentId = $originalSchedule->class_course_assignment_id;

        // Tạo một bản ghi Schedule mới cho buổi dạy bù
        $newSchedule = Schedule::create([
            'class_course_assignment_id' => $assignmentId,
            'room_id' => $validated['new_room_id'],
            'date' => $validated['new_schedule_date'],
            'session' => $validated['new_session'],
            'topic' => 'Dạy bù cho ngày ' . $originalSchedule->date->format('d/m/Y'), // Ví dụ topic
            'status' => 'makeup', // Đánh dấu là lịch dạy bù
        ]);
        // --- Kết thúc Logic tạo lịch dạy mới ---

        // Tạo bản ghi MakeupClass để liên kết
        $makeupClass = MakeupClass::create([
            'teacher_id' => $validated['teacher_id'], // Thay bằng Auth::id()
            'original_schedule_id' => $validated['original_schedule_id'],
            'new_schedule_id' => $newSchedule->id, // Liên kết đến lịch bù vừa tạo
            'status' => 'pending', // Trạng thái chờ duyệt
            // Lưu ghi chú vào bảng schedules hoặc makeup_classes tùy thiết kế
            // 'note' => $validated['note']
        ]);

        // Cập nhật trạng thái lịch dạy gốc thành 'cancelled' hoặc 'makeup_pending'?
        // $originalSchedule->update(['status' => 'cancelled']); // Cân nhắc kỹ logic này

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
