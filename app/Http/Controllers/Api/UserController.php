<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Schedule; 
use Carbon\Carbon;
class UserController extends Controller
{
    /**
     * Lấy danh sách tất cả người dùng
     */
    public function index()
    {
        $users = User::paginate(50);
        return response()->json($users, 200);
    }

    /**
     * Tạo mới người dùng
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:6',
            'phone_number'  => 'required|string|max:20',
            'avatar_url'    => 'nullable|string',
            'gender'        => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => 'nullable|date',
            'role'          => ['required', Rule::in(['student', 'teacher', 'training_office', 'head_of_department'])],
            'status'        => ['required', Rule::in(['active', 'inactive', 'banned'])],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'message' => 'Tạo tài khoản thành công',
            'data'    => $user,
        ], 201);
    }

    /**
     * Xem thông tin 1 người dùng
     */
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy người dùng'], 404);
        }
        return response()->json($user, 200);
    }

    /**
     * Cập nhật thông tin người dùng
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy người dùng'], 404);
        }

        $validated = $request->validate([
            'name'          => 'sometimes|required|string|max:255',
            'first_name'    => 'sometimes|required|string|max:100',
            'last_name'     => 'sometimes|required|string|max:100',
            'email'         => ['sometimes','required','email', Rule::unique('users','email')->ignore($user->id)],
            'password'      => 'nullable|string|min:6',
            'phone_number'  => 'sometimes|required|string|max:20',
            'avatar_url'    => 'nullable|string',
            'gender'        => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => 'nullable|date',
            'role'          => ['sometimes', Rule::in(['student', 'teacher', 'training_office', 'head_of_department'])],
            'status'        => ['sometimes', Rule::in(['active', 'inactive', 'banned'])],
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Cập nhật thông tin thành công',
            'data'    => $user,
        ], 200);
    }

    /**
     * Xóa người dùng
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy người dùng'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Xóa người dùng thành công'], 200);
    }
    public function getHomeSummary(User $user)
    {
        // 1. Lấy ngày giờ hiện tại
        $today = Carbon::today();
        $now = Carbon::now();

        // 2. Lấy danh sách lịch dạy hôm nay (SỬA LẠI TÊN CỘT)
        $todaySchedulesQuery = Schedule::where('date', $today) // 👈 SỬA: 'teaching_date' -> 'date'
            ->whereHas('classCourseAssignment', function($query) use ($user) {
                // 👈 SỬA: 'user_id' -> 'teacher_id'
                $query->where('teacher_id', $user->id); 
            })
            ->with([
                // 👈 SỬA: Không có 'room.building'
                'room', 
                'classCourseAssignment.course',
                // Giả sử Model 'ClassCourseAssignment' có hàm 'classModel' trỏ đến bảng 'classes'
                'classCourseAssignment.classModel' 
            ])
            // 👈 SỬA: Không có 'lesson_start', sắp xếp theo 'session'
            ->orderBy('session', 'asc'); 

        $schedules = $todaySchedulesQuery->get();

        // 3. Tính toán thông tin Summary
        
        // 3.1. Tổng số tiết hôm nay 
        // ⚠️ LƯU Ý: Không thể tính chính xác. Tạm thời đếm số lượng buổi học.
        // Bạn nên sửa DB, tách 'session' thành 'lesson_start', 'lesson_end'
        $todayLessonsCount = $schedules->count(); 

        // 3.2. Tổng số tiết tuần này
        $startOfWeek = $today->copy()->startOfWeek();
        $endOfWeek = $today->copy()->endOfWeek();
        
        $weekLessonsCount = Schedule::whereBetween('date', [$startOfWeek, $endOfWeek]) // 👈 SỬA: 'teaching_date' -> 'date'
             ->whereHas('classCourseAssignment', function($query) use ($user) {
                $query->where('teacher_id', $user->id); // 👈 SỬA: 'user_id' -> 'teacher_id'
             })
             ->count(); // 👈 SỬA: Tạm thời đếm số lượng
        
        // 3.3. Phần trăm hoàn thành (ví dụ)
        $completionPercent = 0.0; 

        // 4. Định dạng lại danh sách lịch dạy (SỬA LẠI TÊN CỘT)
        $formattedSchedules = $schedules->map(function($schedule) use ($now) {
            
            // 👈 SỬA: Không có 'building_name'. Dùng 'location' từ bảng 'rooms'
            $location = $schedule->room?->location ?? 'N/A';
            // 👈 SỬA: 'room_name' -> 'name'
            $roomName = $schedule->room?->name ?? 'N/A';
            
            $courseName = $schedule->classCourseAssignment?->course?->name ?? 'N/A';
            
            // 👈 SỬA: Không có 'class_code'. Dùng 'name' từ bảng 'classes'
            $classCode = $schedule->classCourseAssignment?->classModel?->name ?? 'N/A';

            // ⚠️ LƯU Ý: Không thể tính status 'Đang diễn ra' vì không có time_start/time_end.
            // Lấy trực tiếp status từ DB ('scheduled', 'taught', ...)
            $status = $schedule->status;

            return [
                'id' => $schedule->id,
                // 👈 SỬA: 'time_range' và 'lessons' sẽ dùng chung cột 'session'
                'time_range' => $schedule->session, 
                'lessons' => $schedule->session,
                'title' => $courseName,
                'course_code' => "({$classCode})", 
                // 👈 SỬA: Kết hợp room name và location
                'location' => "{$roomName} - {$location}",
                'status' => $status,
            ];
        });

        // 5. Trả về JSON theo đúng cấu trúc HomeSummary.dart
        return response()->json([
            'summary' => [
                'today_lessons' => $todayLessonsCount,
                'week_lessons' => $weekLessonsCount,
                'completion_percent' => $completionPercent,
            ],
            'today_schedules' => $formattedSchedules,
        ]);
    }
}
