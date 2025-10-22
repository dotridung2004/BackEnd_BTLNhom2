<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    // ... (Các hàm index, store, show, update, destroy bị comment giữ nguyên) ...

    /**
     * Lấy danh sách sinh viên và trạng thái điểm danh.
     * Phiên bản ĐÚNG (không dùng 'date')
     */
    public function getStudentsAndAttendance(Request $request, Schedule $schedule)
    {
        // 1. Lấy danh sách sinh viên
        $students = $schedule->classCourseAssignment
                              ->classModel
                              ?->students()
                              ->orderBy('name', 'asc')
                              ->get(['users.id', 'users.name']);

        if (!$students || $students->isEmpty()) {
            return response()->json([]); // Trả về mảng rỗng nếu không có sinh viên
        }

        // 2. Lấy điểm danh đã có (Đã xóa where('date'))
        $existingAttendance = Attendance::where('schedule_id', $schedule->id)
                                        ->whereIn('student_id', $students->pluck('id'))
                                        ->pluck('status', 'student_id');

        // 3. Kết hợp kết quả
        $results = $students->map(function ($student) use ($existingAttendance) {
            return [
                'student_id'   => $student->id,
                'student_name' => $student->name,
                'status'       => $existingAttendance->get($student->id, 'present') // Mặc định là 'present'
            ];
        });

        return response()->json($results);
    }

    /**
     * Lưu điểm danh hàng loạt
     * Phiên bản ĐÚNG (không dùng 'date')
     */
    public function saveBulkAttendance(Request $request)
    {
        // 1. Validate (Đã xóa 'date')
        $validated = $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:users,id',
            'attendances.*.status' => ['required', Rule::in(['present', 'absent', 'late'])],
        ]);

        $scheduleId = $validated['schedule_id'];
        $attendanceData = $validated['attendances'];

        // 2. Lưu vào DB (Đã xóa 'date')
        DB::beginTransaction();
        try {
            foreach ($attendanceData as $att) {
                Attendance::updateOrCreate(
                    [
                        'schedule_id' => $scheduleId,
                        'student_id'  => $att['student_id'],
                        // 'date' => $date, // <--- ĐÃ XÓA
                    ],
                    [
                        'status' => $att['status'],
                    ]
                );
            }
            DB::commit();
            return response()->json(['message' => 'Lưu điểm danh thành công!'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Lỗi Lưu Điểm Danh Hàng Loạt: " . $e->getMessage());
            return response()->json(['message' => 'Đã xảy ra lỗi khi lưu điểm danh.', 'error' => $e->getMessage()], 500);
        }
    }

    // --- HÀM saveBulkAttendance BỊ TRÙNG LẶP ĐÃ BỊ XÓA KHỎI ĐÂY ---
    
}