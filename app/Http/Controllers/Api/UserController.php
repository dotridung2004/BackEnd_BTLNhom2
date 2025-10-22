<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Schedule; 
use Carbon\Carbon;
use App\Models\LeaveRequest;
use App\Models\MakeupClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // 👈 *** THÊM DÒNG NÀY ***

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

    // --- API CHO TRANG CHỦ GIÁO VIÊN ---

    public function getHomeSummary(User $user)
    {
        $today = Carbon::today();
        $now = Carbon::now();

        $todaySchedulesQuery = Schedule::where('date', $today)
            ->whereHas('classCourseAssignment', function($query) use ($user) {
                $query->where('teacher_id', $user->id); 
            })
            ->with(['room', 'classCourseAssignment.course', 'classCourseAssignment.classModel'])
            ->orderBy('session', 'asc'); 

        $schedules = $todaySchedulesQuery->get();
        
        $todayLessonsCount = $schedules->count(); 

        $startOfWeek = $today->copy()->startOfWeek();
        $endOfWeek = $today->copy()->endOfWeek();
        
        $weekLessonsCount = Schedule::whereBetween('date', [$startOfWeek, $endOfWeek])
             ->whereHas('classCourseAssignment', function($query) use ($user) {
                $query->where('teacher_id', $user->id);
             })
             ->count();
        
        $completionPercent = 0.0; // Placeholder

        $formattedSchedules = $schedules->map(function($schedule) use ($now) {
            $location = $schedule->room?->location ?? 'N/A';
            $roomName = $schedule->room?->name ?? 'N/A';
            $courseName = $schedule->classCourseAssignment?->course?->name ?? 'N/A';
            $classCode = $schedule->classCourseAssignment?->classModel?->name ?? 'N/A';
            $status = $schedule->status; // (Nên có logic tính 'Đang diễn ra')

            return [
                'id' => $schedule->id,
                'time_range' => $schedule->session, 
                'lessons' => $schedule->session,
                'title' => $courseName,
                'course_code' => "({$classCode})", 
                'location' => "{$roomName} - {$location}",
                'status' => $status,
            ];
        });

        return response()->json([
            'summary' => [
                'today_lessons' => $todayLessonsCount,
                'week_lessons' => $weekLessonsCount,
                'completion_percent' => $completionPercent,
            ],
            'today_schedules' => $formattedSchedules,
        ]);
    }

    // --- API CHO LỊCH DẠY GIÁO VIÊN ---
    
    public function getScheduleData(Request $request, User $user)
    {
        $weekOffset = (int)$request->query('week_offset', 0);
        $today = Carbon::today();
        $startOfWeek = $today->copy()->addWeeks($weekOffset)->startOfWeek(); 

        $todaySchedules = $this->getSchedulesForDates($user, [Carbon::today()]);
        $todayData = [
            'day_number' => $today->format('d'),
            'full_date_string' => $this->formatFullDateString($today),
            'schedules' => $this->formatSchedules($todaySchedules, Carbon::now()),
        ];

        $weekDates = [];
        $dateCursor = $startOfWeek->copy();
        $todayIndex = 0;
        for ($i = 0; $i < 7; $i++) {
            $weekDates[] = [
                'day_name' => $this->formatDayName($dateCursor),
                'day_number' => $dateCursor->format('d'),
                'full_date' => $dateCursor->toDateString(),
                'full_date_string' => $this->formatFullDateString($dateCursor),
            ];
            if ($dateCursor->isSameDay(Carbon::today())) {
                $todayIndex = $i;
            }
            $dateCursor->addDay();
        }

        $endOfWeek = $startOfWeek->copy()->addDays(6);
        $allWeekSchedules = $this->getSchedulesForDates($user, [$startOfWeek, $endOfWeek]);

        $schedulesByDate = [];
        foreach ($weekDates as $date) {
            $schedulesByDate[$date['full_date']] = [];
        }

        $formattedSchedulesMap = $allWeekSchedules->groupBy(function($schedule) {
            return $schedule->date->toDateString();
        });

        foreach ($formattedSchedulesMap as $dateKey => $schedules) {
            if (isset($schedulesByDate[$dateKey])) {
                $schedulesByDate[$dateKey] = $this->formatSchedules($schedules, Carbon::now());
            }
        }

        $weekData = [
            'dates' => $weekDates,
            'today_index' => $todayIndex,
            'schedules_by_date' => $schedulesByDate,
        ];

        return response()->json([
            'today' => $todayData,
            'week' => $weekData,
        ]);
    }

    // --- API CHO BÁO CÁO GIÁO VIÊN ---
    
    public function getReportData(Request $request, User $user)
    {
        $validated = $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date'   => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);
        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        $schedules = $this->getSchedulesForDates($user, [$startDate, $endDate]);

        // (Đã sửa để dùng Auth::id() thay vì $user->id)
        $teacherId = Auth::id() ?? $user->id; // Ưu tiên Auth::id()

        $totalSessions = $schedules->count();
        $absenceCount = LeaveRequest::where('teacher_id', $teacherId)
                            ->where('status', 'approved')
                            ->whereHas('schedule', function ($q) use ($startDate, $endDate) {
                                $q->whereBetween('date', [$startDate, $endDate]);
                            })
                            ->count();
        $makeupCount = MakeupClass::where('teacher_id', $teacherId)
                             ->whereIn('status', ['approved', 'done'])
                             ->whereHas('originalSchedule', function ($q) use ($startDate, $endDate) {
                                 $q->whereBetween('date', [$startDate, $endDate]);
                             })
                             ->count();
        $attendanceRate = 95.5; // Placeholder

        $chartData = [
            ['label' => 'Tổng buổi', 'value' => $totalSessions],
            ['label' => 'Nghỉ', 'value' => $absenceCount],
            ['label' => 'Dạy bù', 'value' => $makeupCount],
        ];

        $detailedList = $this->formatSchedulesForReport($schedules);

        return response()->json([
            'summary' => [
                'total_sessions' => $totalSessions,
                'absences_count' => $absenceCount,
                'makeups_count' => $makeupCount,
                'attendance_rate' => round($attendanceRate, 1),
            ],
            'chart_data' => $chartData,
            'details' => $detailedList,
        ]);
    }
    
    // --- API CHO NGHỈ/BÙ GIÁO VIÊN ---

    public function getLeaveMakeupSummary(User $user)
    {
        $teacherId = Auth::id() ?? $user->id; // Ưu tiên Auth::id()

        $approvedLeaveCount = LeaveRequest::where('teacher_id', $teacherId)
                                            ->where('status', 'approved')
                                            ->count();
        $pendingMakeupCount = LeaveRequest::where('teacher_id', $teacherId)
                                            ->where('status', 'approved')
                                            ->whereDoesntHave('makeupClass', function ($query) {
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
        $teacherId = Auth::id() ?? $user->id; // Ưu tiên Auth::id()

        $pendingLeaves = LeaveRequest::where('teacher_id', $teacherId)
                                      ->where('status', 'approved')
                                      ->whereDoesntHave('makeupClass', function ($query) {
                                          $query->whereIn('status', ['pending', 'approved', 'done']);
                                      })
                                      ->with(['schedule.room', 'schedule.classCourseAssignment.course', 'schedule.classCourseAssignment.classModel'])
                                      ->get();

        $formatted = $pendingLeaves->map(function($leaveRequest) {
            $schedule = $leaveRequest->schedule;
            if (!$schedule) return null;
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
            ];
        })->whereNotNull();

        return response()->json($formatted->values());
    }

    // --- API CHO TRANG CHỦ SINH VIÊN ---

    public function getStudentHomeSummary(User $user)
    {
        if ($user->role !== 'student') {
            return response()->json(['message' => 'Tài khoản không phải là sinh viên'], 403);
        }

        $today = Carbon::today();
        $now = Carbon::now();

        $studentClassIds = DB::table('class_student')
                             ->where('student_id', $user->id)
                             ->pluck('class_model_id');
        
        $assignmentIds = DB::table('class_course_assignments')
                             ->whereIn('class_id', $studentClassIds)
                             ->pluck('id');

        $todaySchedules = Schedule::where('date', $today)
            ->whereIn('class_course_assignment_id', $assignmentIds)
            ->with([
                'room', 
                'classCourseAssignment.course',
                'classCourseAssignment.classModel',
                'classCourseAssignment.teacher'
            ])
            ->orderBy('session', 'asc')
            ->get();

        $todaySessionCount = $todaySchedules->count();
        
        $startOfWeek = $today->copy()->startOfWeek();
        $endOfWeek = $today->copy()->endOfWeek();

        $weekSessionCount = Schedule::whereBetween('date', [$startOfWeek, $endOfWeek])
            ->whereIn('class_course_assignment_id', $assignmentIds)
            ->count();
        
        $attendanceRate = 95.0; // Dữ liệu giả

        $formattedSchedules = $todaySchedules->map(function($schedule) use ($now) {
            $courseName = $schedule->classCourseAssignment?->course?->name ?? 'N/A';
            $classCode = $schedule->classCourseAssignment?->classModel?->name ?? 'N/A';
            $teacherName = $schedule->classCourseAssignment?->teacher?->name ?? 'N/A';
            $location = $schedule->room?->name ?? 'N/A';
            $status = 'Sắp diễn ra'; // (Placeholder)

            return [
                'id' => $schedule->id,
                'time_range' => $schedule->session, 
                'lessons' => $schedule->session,
                'title' => $courseName,
                'course_code' => "({$classCode})", 
                'location' => $location,
                'status' => $status,
                'teacher_name' => $teacherName,
            ];
        });

        return response()->json([
            'summary' => [
                'today_sessions' => $todaySessionCount,
                'week_sessions' => $weekSessionCount,
                'attendance_rate' => $attendanceRate,
            ],
            'today_schedules' => $formattedSchedules,
        ]);
    }

    // --- CÁC HÀM HỖ TRỢ (PRIVATE) ---

    private function getSchedulesForDates(User $user, array $dateRange)
    {
        // (Sửa lỗi bảo mật: hàm này chỉ nên dùng cho giáo viên, nên dùng Auth::id())
        $teacherId = Auth::id() ?? $user->id;

        $query = Schedule::whereHas('classCourseAssignment', function($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId); 
        })
        ->with(['room', 'classCourseAssignment.course', 'classCourseAssignment.classModel']);

        if (count($dateRange) == 1) {
            $query->where('date', $dateRange[0]);
        } else {
            $query->whereBetween('date', $dateRange);
        }

        return $query->orderBy('session', 'asc')->get();
    }

    private function formatSchedules($schedules, Carbon $now)
    {
        return $schedules->map(function($schedule) use ($now) {
            $location = $schedule->room?->location ?? 'N/A';
            $roomName = $schedule->room?->name ?? 'N/A';
            $courseName = $schedule->classCourseAssignment?->course?->name ?? 'N/A';
            $classCode = $schedule->classCourseAssignment?->classModel?->name ?? 'N/A';
            $status = $schedule->status; 

            return [
                'id' => $schedule->id,
                'time' => $schedule->session,
                'lessons' => $schedule->session,
                'title' => $courseName,
                'course_code' => "({$classCode})", 
                'location' => "{$roomName} - {$location}",
                'status' => $status,
            ];
        });
    }

    private function formatSchedulesForReport($schedules)
    {
        return $this->formatSchedules($schedules, Carbon::now())
            ->map(function($formattedSchedule) use ($schedules) {
                /** @var \App\Models\Schedule|null $originalSchedule */
                $originalSchedule = $schedules->firstWhere('id', $formattedSchedule['id']);
                $formattedSchedule['date_string'] = $originalSchedule ? $originalSchedule->date->format('d/m') : 'N/A';
                $formattedSchedule['students'] = 'N/A'; // Placeholder
                $formattedSchedule['attendance'] = 'N/A'; // Placeholder
                return $formattedSchedule;
            });
    }

    private function formatDayName(Carbon $date)
    {
        if ($date->isSunday()) return 'CN';
        return 'T' . ($date->dayOfWeek + 1); 
    }

    private function formatFullDateString(Carbon $date)
    {
        return $date->locale('vi')->translatedFormat('l, d/m/Y');
    }

    public function getStudentWeeklySchedule(User $user)
    {
        if ($user->role !== 'student') {
            return response()->json(['message' => 'Tài khoản không phải là sinh viên'], 403);
        }
        
        // (Thêm kiểm tra bảo mật: chỉ cho phép sinh viên tự xem)
        if (Auth::id() != $user->id) {
             return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }

        // 1. Xác định ngày trong tuần
        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek(); // Bắt đầu từ Thứ 2
        $endOfWeek = $today->copy()->endOfWeek();     // Kết thúc vào Chủ Nhật

        // 2. Lấy ID các lớp sinh viên này học
        $studentClassIds = DB::table('class_student')
                            ->where('student_id', $user->id)
                            ->pluck('class_model_id');
        
        $assignmentIds = DB::table('class_course_assignments')
                            ->whereIn('class_id', $studentClassIds)
                            ->pluck('id');

        // 3. Truy vấn tất cả lịch học trong tuần
        $allWeekSchedules = Schedule::whereBetween('date', [$startOfWeek, $endOfWeek])
            ->whereIn('class_course_assignment_id', $assignmentIds)
            ->with([
                'room', 
                'classCourseAssignment.course',
                'classCourseAssignment.classModel',
                'classCourseAssignment.teacher'
            ])
            ->orderBy('date', 'asc')      // Sắp xếp theo ngày
            ->orderBy('session', 'asc')   // Rồi sắp xếp theo tiết
            ->get();

        // 4. Định dạng lại dữ liệu (Quan trọng: phải có 'schedule_date')
        $formattedSchedules = $allWeekSchedules->map(function($schedule) {
            $courseName = $schedule->classCourseAssignment?->course?->name ?? 'N/A';
            $classCode = $schedule->classCourseAssignment?->classModel?->name ?? 'N/A';
            $teacherName = $schedule->classCourseAssignment?->teacher?->name ?? 'N/A';
            $location = $schedule->room?->name ?? 'N/A';
            $status = 'Sắp diễn ra'; // (Placeholder)

            return [
                'id' => $schedule->id,
                'time_range' => $schedule->session, 
                'lessons' => $schedule->session,
                'title' => $courseName,
                'course_code' => $classCode,
                'location' => $location,
                'status' => $status,
                'teacher_name' => $teacherName,
                // 👇 *** Rất quan trọng: Thêm trường này cho Flutter ***
                'schedule_date' => $schedule->date->toIso8601String(), 
            ];
        });

        // 5. Trả về một danh sách (List)
        return response()->json($formattedSchedules);
    }
}