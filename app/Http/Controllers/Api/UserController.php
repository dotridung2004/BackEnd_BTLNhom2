<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Schedule; 
use Carbon\Carbon;
use App\Models\LeaveRequest; // 👈 Add
use App\Models\MakeupClass;  // 👈 Add
use Illuminate\Support\Facades\DB;
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
    public function getScheduleData(Request $request, User $user)
    {
        // 1. Lấy thông số tuần (0 = tuần này, -1 = tuần trước, 1 = tuần sau)
        $weekOffset = (int)$request->query('week_offset', 0);
        $today = Carbon::today();
        
        // Tính ngày bắt đầu của tuần (Thứ 2) dựa trên offset
        $startOfWeek = $today->copy()->addWeeks($weekOffset)->startOfWeek(); 

        // 2. Lấy dữ liệu cho tab "Hôm nay" (Tương tự getHomeSummary)
        $todaySchedules = $this->getSchedulesForDates($user, [Carbon::today()]);
        $todayData = [
            'day_number' => $today->format('d'),
            'full_date_string' => $this->formatFullDateString($today),
            'schedules' => $this->formatSchedules($todaySchedules, Carbon::now()),
        ];

        // 3. Lấy dữ liệu cho tab "Tuần này"
        
        // 3.1. Tạo mảng 7 ngày trong tuần
        $weekDates = [];
        $dateCursor = $startOfWeek->copy();
        $todayIndex = 0;

        for ($i = 0; $i < 7; $i++) {
            $weekDates[] = [
                'day_name' => $this->formatDayName($dateCursor), // "T2", "T3"
                'day_number' => $dateCursor->format('d'),
                'full_date' => $dateCursor->toDateString(), // "2025-10-20"
                'full_date_string' => $this->formatFullDateString($dateCursor), // "Thứ 2, Ngày 20/10/2025"
            ];
            
            if ($dateCursor->isSameDay(Carbon::today())) {
                $todayIndex = $i;
            }
            $dateCursor->addDay();
        }

        // 3.2. Lấy *TẤT CẢ* lịch dạy trong 7 ngày đó
        $endOfWeek = $startOfWeek->copy()->addDays(6);
        $allWeekSchedules = $this->getSchedulesForDates($user, [$startOfWeek, $endOfWeek]);

        // 3.3. Nhóm lịch dạy theo ngày (Map<String, List<Schedule>>)
        $schedulesByDate = [];
        // Khởi tạo map với các mảng rỗng
        foreach ($weekDates as $date) {
            $schedulesByDate[$date['full_date']] = [];
        }

        // Phân loại lịch dạy vào đúng ngày
        $formattedSchedulesMap = $allWeekSchedules->groupBy(function($schedule) {
            return $schedule->date->toDateString();
        });

        foreach ($formattedSchedulesMap as $dateKey => $schedules) {
            $schedulesByDate[$dateKey] = $this->formatSchedules($schedules, Carbon::now());
        }

        $weekData = [
            'dates' => $weekDates,
            'today_index' => $todayIndex,
            'schedules_by_date' => $schedulesByDate,
        ];

        // 4. Trả về JSON hoàn chỉnh
        return response()->json([
            'today' => $todayData,
            'week' => $weekData,
        ]);
    }


    /**
     * HÀM HỖ TRỢ 1: Query lịch dạy
     * (Tách ra từ getHomeSummary để tái sử dụng)
     */
    private function getSchedulesForDates(User $user, array $dateRange)
    {
        $query = Schedule::whereHas('classCourseAssignment', function($q) use ($user) {
                $q->where('teacher_id', $user->id); 
            })
            ->with(['room', 'classCourseAssignment.course', 'classCourseAssignment.classModel']);

        if (count($dateRange) == 1) {
            $query->where('date', $dateRange[0]); // Lấy 1 ngày
        } else {
            $query->whereBetween('date', $dateRange); // Lấy khoảng (tuần)
        }

        return $query->orderBy('session', 'asc')->get();
    }

    /**
     * HÀM HỖ TRỢ 2: Format lịch dạy
     * (Tách ra từ getHomeSummary để tái sử dụng)
     */
    private function formatSchedules($schedules, Carbon $now)
    {
        return $schedules->map(function($schedule) use ($now) {
            $location = $schedule->room?->location ?? 'N/A';
            $roomName = $schedule->room?->name ?? 'N/A';
            $courseName = $schedule->classCourseAssignment?->course?->name ?? 'N/A';
            $classCode = $schedule->classCourseAssignment?->classModel?->name ?? 'N/A';
            
            // ⚠️ LƯU Ý: Vẫn dùng status từ DB vì không có time_start/time_end
            $status = $schedule->status; 

            return [
                'id' => $schedule->id,
                'time' => $schedule->session, // 'time' là key frontend dùng
                'lessons' => $schedule->session,
                'title' => $courseName,
                'course_code' => "({$classCode})", 
                'location' => "{$roomName} - {$location}",
                'status' => $status,
            ];
        });
    }

    /**
     * HÀM HỖ TRỢ 3: Format tên Thứ
     */
    private function formatDayName(Carbon $date)
    {
        if ($date->isSunday()) return 'CN';
        // 'N' trả về 1 (Thứ 2) -> 7 (Chủ Nhật)
        return 'T' . ($date->dayOfWeek + 1); 
    }

    /**
     * HÀM HỖ TRỢ 4: Format ngày đầy đủ
     */
    private function formatFullDateString(Carbon $date)
    {
        // ⛔️ THAY THẾ DÒNG CŨ:
        // return $date->locale('vi')->formatPattern("'Thứ' EEEE, 'Ngày' dd/MM/yyyy");

        // ✅ BẰNG DÒNG MỚI:
        // 'l' = Tên thứ đầy đủ (ví dụ: "Thứ Ba")
        // 'd/m/Y' = Ngày/Tháng/Năm
        return $date->locale('vi')->translatedFormat('l, d/m/Y');
    }
    public function getReportData(Request $request, User $user)
    {
        // 1. Validate Input Dates
        $validated = $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);
        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        // 2. Query Schedules in Date Range
        // Use the existing helper function but for a range
        $schedules = $this->getSchedulesForDates($user, [$startDate, $endDate]);

        // 3. Calculate Summary Statistics (EXAMPLES - refine these!)
        //    - Total Hours/Periods: Needs logic based on 'session' or better columns.
        //      Let's just count sessions for now as a placeholder.
        $totalSessions = $schedules->count();
        //    - Absences: Count approved leave requests linked to these schedules
        $absenceCount = LeaveRequest::where('teacher_id', $user->id)
                            ->where('status', 'approved')
                            // Ideally, link LeaveRequest directly to schedule_id
                            // This assumes leave date is enough (might be inaccurate)
                            ->whereBetween('created_at', [$startDate, $endDate]) // Or use a specific 'leave_date' column
                            ->count();
        //    - Makeups: Count approved/done makeup classes linked to original schedules in range
        $makeupCount = MakeupClass::where('teacher_id', $user->id)
                             ->whereIn('status', ['approved', 'done'])
                             // Check if the *original* schedule date falls within the range
                             ->whereHas('originalSchedule', function ($q) use ($startDate, $endDate) {
                                 $q->whereBetween('date', [$startDate, $endDate]);
                             })
                             ->count();
        //    - Attendance Rate: This is complex. Requires summing attendance across all students
        //      for all schedules in the range. Let's use a placeholder.
        $attendanceRate = 95.5; // Placeholder - Calculate properly if possible

        // 4. Prepare Chart Data
        $chartData = [
            ['label' => 'Tổng buổi', 'value' => $totalSessions], // Using session count
            ['label' => 'Nghỉ', 'value' => $absenceCount],
            ['label' => 'Dạy bù', 'value' => $makeupCount],
            // Note: Attendance Rate is a percentage, maybe not suitable for simple bar/pie sum
            // You might chart 'Present Sessions' vs 'Absent Sessions' instead
        ];

        // 5. Format Detailed List (Using existing helper)
        $detailedList = $this->formatSchedulesForReport($schedules); // Use a potentially adapted formatting function


        // 6. Return Combined JSON Response
        return response()->json([
            'summary' => [
                'total_sessions' => $totalSessions, // Renamed from total_hours
                'absences_count' => $absenceCount,
                'makeups_count' => $makeupCount,
                'attendance_rate' => round($attendanceRate, 1), // Round to 1 decimal
            ],
            'chart_data' => $chartData,
            'details' => $detailedList,
        ]);
    }

    /**
     * HÀM HỖ TRỢ (ADAPTED): Format lịch dạy specifically for the report list
     * (Could be similar to formatSchedules but might need minor adjustments like date format)
     */
    private function formatSchedulesForReport($schedules)
    {
        // Using formatSchedules for now, adapt if report needs different fields/formats
        return $this->formatSchedules($schedules, Carbon::now()) // Pass Carbon::now() or handle differently if needed
            ->map(function($formattedSchedule) use ($schedules) {
                 // Add the date to each item for frontend display convenience
                 $originalSchedule = $schedules->firstWhere('id', $formattedSchedule['id']);
                 $formattedSchedule['date_string'] = $originalSchedule ? $originalSchedule->date->format('d/m') : 'N/A'; // Add formatted date
                 // You might also add student count / attendance % per session here if feasible
                 $formattedSchedule['students'] = 'N/A'; // Placeholder
                 $formattedSchedule['attendance'] = 'N/A'; // Placeholder
                 return $formattedSchedule;
            });
    }
    public function getLeaveMakeupSummary(User $user)
    {
        // Đếm số buổi đã nghỉ (đơn xin nghỉ được duyệt)
        $approvedLeaveCount = LeaveRequest::where('teacher_id', $user->id)
                                        ->where('status', 'approved')
                                        ->count();

        // Đếm số buổi cần bù (nghỉ được duyệt NHƯNG chưa có trong makeup_classes hoặc makeup bị từ chối)
        $pendingMakeupCount = LeaveRequest::where('teacher_id', $user->id)
                                        ->where('status', 'approved')
                                        ->whereDoesntHave('makeupClass', function ($query) {
                                            // Chỉ tính những đơn nghỉ chưa có lớp bù (hoặc lớp bù bị từ chối)
                                            $query->whereIn('status', ['pending', 'approved', 'done']);
                                        })
                                        ->count();

        return response()->json([
            'leave_count' => $approvedLeaveCount,
            'pending_makeup_count' => $pendingMakeupCount,
        ]);
    }

    public function getPendingMakeupSchedules(User $user)
    {
        // Lấy các đơn nghỉ được duyệt mà chưa có lớp bù tương ứng
        $pendingLeaves = LeaveRequest::where('teacher_id', $user->id)
                                    ->where('status', 'approved')
                                    ->whereDoesntHave('makeupClass', function ($query) {
                                        $query->whereIn('status', ['pending', 'approved', 'done']);
                                    })
                                    // Load thông tin lịch dạy gốc để hiển thị
                                    ->with(['schedule.room', 'schedule.classCourseAssignment.course', 'schedule.classCourseAssignment.classModel'])
                                    ->get();

        // Format lại dữ liệu giống ScheduleCard/ScheduleDetailItem
        $formatted = $pendingLeaves->map(function($leaveRequest) {
            $schedule = $leaveRequest->schedule;
            if (!$schedule) return null; // Bỏ qua nếu lịch dạy gốc không tồn tại

            $location = $schedule->room?->location ?? 'N/A';
            $roomName = $schedule->room?->name ?? 'N/A';
            $courseName = $schedule->classCourseAssignment?->course?->name ?? 'N/A';
            $classCode = $schedule->classCourseAssignment?->classModel?->name ?? 'N/A';

            return [
                'leave_request_id' => $leaveRequest->id, // ID của đơn xin nghỉ
                'schedule_id' => $schedule->id,         // ID của lịch dạy gốc
                'date_string' => $schedule->date->format('d/m/Y'), // Thêm ngày nghỉ
                'time_range' => $schedule->session,
                'lesson_period' => $schedule->session,
                'subject_name' => $courseName,
                'course_code' => "({$classCode})",
                'location' => "{$roomName} - {$location}",
                // Thêm các thông tin khác nếu cần
            ];
        })->whereNotNull(); // Loại bỏ các kết quả null

        return response()->json($formatted->values()); // Trả về mảng JSON
    }
}
