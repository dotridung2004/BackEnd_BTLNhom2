<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request; // 👈 *** ĐẢM BẢO CÓ DÒNG NÀY ***
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Schedule;
use Carbon\Carbon;
use App\Models\LeaveRequest;
use App\Models\MakeupClass;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // ... (Các hàm CRUD: index, store, show, update, destroy vẫn giữ nguyên) ...
    // ... (Mình sẽ ẩn đi cho gọn) ...

    /**
     * @OA\Get(
     * path="/api/users",
     * ... (giữ nguyên) ...
     * )
     */
    public function index(Request $request) // 👈 SỬA 1: Thêm Request $request
    {
        $searchQuery = $request->input('name');
        $query = User::query();
        $query->when($searchQuery, function ($q) use ($searchQuery) {
            return $q->where('name', 'like', '%' . $searchQuery . '%');
        });
        $users = $query->latest()->paginate(10);
        return response()->json($users, 200);
    }

    /**
     * @OA\Post(
     * path="/api/users",
     * ... (giữ nguyên) ...
     * )
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
     * @OA\Get(
     * path="/api/users/{user}",
     * ... (giữ nguyên) ...
     * )
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
     * @OA\Put(
     * path="/api/users/{user}",
     * ... (giữ nguyên) ...
     * )
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
            'email'         => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
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
     * @OA\Delete(
     * path="/api/users/{user}",
     * ... (giữ nguyên) ...
     * )
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

    /**
     * @OA\Get(
     * path="/api/users/{user}/home-summary",
     * ... (giữ nguyên) ...
     * )
     */
    public function getHomeSummary(User $user)
    {
        $today = Carbon::today();
        $now = Carbon::now();

        $todaySchedulesQuery = Schedule::where('date', $today)
            ->whereHas('classCourseAssignment', function ($query) use ($user) {
                $query->where('teacher_id', $user->id);
            })
            ->with(['room', 'classCourseAssignment.course', 'classCourseAssignment.classModel'])
            ->orderBy('session', 'asc');

        $schedules = $todaySchedulesQuery->get();

        $todayLessonsCount = $schedules->count();

        $startOfWeek = $today->copy()->startOfWeek();
        $endOfWeek = $today->copy()->endOfWeek();

        $weekLessonsCount = Schedule::whereBetween('date', [$startOfWeek, $endOfWeek])
            ->whereHas('classCourseAssignment', function ($query) use ($user) {
                $query->where('teacher_id', $user->id);
            })
            ->count();

        $completionPercent = 0.0; // Placeholder

        $formattedSchedules = $schedules->map(function ($schedule) use ($now) {
            $roomName = $schedule->room?->name ?? 'N/A'; // Lấy tên phòng
            $courseName = $schedule->classCourseAssignment?->course?->name ?? 'N/A';
            $classCode = $schedule->classCourseAssignment?->classModel?->name ?? 'N/A';
            $status = $schedule->status; // (Nên có logic tính 'Đang diễn ra')

            return [
                'id' => $schedule->id,
                'time_range' => $schedule->session, // (Flutter sẽ tự đổi 4-6 thành giờ)
                'lessons' => $schedule->session,
                'title' => $courseName,
                'course_code' => "({$classCode})",

                // <<< SỬA LỖI N/A 1: Đổi 'room_name' thành 'location' >>>
                // Key 'location' phải khớp với model TeachingSchedule trong Flutter
                'location' => $roomName, 
                
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
    
    /**
     * @OA\Get(
     * path="/api/users/{user}/schedule-data",
     * ... (giữ nguyên) ...
     * )
     */
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

        $formattedSchedulesMap = $allWeekSchedules->groupBy(function ($schedule) {
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

    
    // --- API CHO NGHỈ/BÙ GIÁO VIÊN ---

    /**
     * @OA\Get(
     * path="/api/users/{user}/leave-makeup-summary",
     * ... (giữ nguyên) ...
     * )
     */
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

    /**
     * @OA\Get(
     * path="/api/users/{user}/pending-makeup",
     * ... (giữ nguyên) ...
     * )
     */
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

        $formatted = $pendingLeaves->map(function ($leaveRequest) {
            $schedule = $leaveRequest->schedule;
            if (!$schedule) return null;
            
            $roomName = $schedule->room?->name ?? 'N/A'; // Lấy tên phòng
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
                
                // <<< SỬA LỖI N/A 2: Đổi 'room_name' thành 'location' >>>
                // Key 'location' phải khớp với model PendingMakeupItem trong Flutter
                'location' => $roomName,
            ];
        })->whereNotNull();

        return response()->json($formatted->values());
    }

    // --- API CHO TRANG CHỦ SINH VIÊN ---

    /**
     * @OA\Get(
     * path="/api/students/{user}/home-summary",
     * ... (giữ nguyên) ...
     * )
     */
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

        $formattedSchedules = $todaySchedules->map(function ($schedule) use ($now) {
            $courseName = $schedule->classCourseAssignment?->course?->name ?? 'N/A';
            $classCode = $schedule->classCourseAssignment?->classModel?->name ?? 'N/A';
            $teacherName = $schedule->classCourseAssignment?->teacher?->name ?? 'N/A';
            
            // (File này đã trả về 'location' từ $schedule->room?->name -> RẤT TỐT, GIỮ NGUYÊN)
            $location = $schedule->room?->name ?? 'N/A'; 
            $status = 'Sắp diễn ra'; // (Placeholder)

            return [
                'id' => $schedule->id,
                'time_range' => $schedule->session,
                'lessons' => $schedule->session,
                'title' => $courseName,
                'course_code' => "({$classCode})",
                'location' => $location, // (Đã đúng)
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
        $teacherId = Auth::id() ?? $user->id;

        $query = Schedule::whereHas('classCourseAssignment', function ($q) use ($teacherId) {
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
        return $schedules->map(function ($schedule) use ($now) {
            $roomName = $schedule->room?->name ?? 'N/A'; // Lấy tên phòng
            $courseName = $schedule->classCourseAssignment?->course?->name ?? 'N/A';
            $classCode = $schedule->classCourseAssignment?->classModel?->name ?? 'N/A';
            $status = $schedule->status;

            return [
                'id' => $schedule->id,
                'time' => $schedule->session,
                'lessons' => $schedule->session,
                'title' => $courseName,
                'course_code' => "({$classCode})",
                
                // <<< SỬA LỖI N/A 3: Đổi 'room_name' thành 'location' >>>
                // Key 'location' phải khớp với model TeachingSchedule trong Flutter
                'location' => $roomName, 
                
                'status' => $status,
            ];
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

    /**
     * @OA\Get(
     * path="/api/students/{user}/schedule/week",
     * ... (giữ nguyên) ...
     * )
     */
    public function getStudentWeeklySchedule(User $user)
    {
        if ($user->role !== 'student') {
            return response()->json(['message' => 'Tài khoản không phải là sinh viên'], 403);
        }
        if (Auth::id() != $user->id) {
            return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }
        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek(); 
        $endOfWeek = $today->copy()->endOfWeek();   

        $studentClassIds = DB::table('class_student')
            ->where('student_id', $user->id)
            ->pluck('class_model_id');

        $assignmentIds = DB::table('class_course_assignments')
            ->whereIn('class_id', $studentClassIds)
            ->pluck('id');

        $allWeekSchedules = Schedule::whereBetween('date', [$startOfWeek, $endOfWeek])
            ->whereIn('class_course_assignment_id', $assignmentIds)
            ->with([
                'room',
                'classCourseAssignment.course',
                'classCourseAssignment.classModel',
                'classCourseAssignment.teacher'
            ])
            ->orderBy('date', 'asc')   
            ->orderBy('session', 'asc') 
            ->get();

        $formattedSchedules = $allWeekSchedules->map(function ($schedule) {
            $courseName = $schedule->classCourseAssignment?->course?->name ?? 'N/A';
            $classCode = $schedule->classCourseAssignment?->classModel?->name ?? 'N/A';
            $teacherName = $schedule->classCourseAssignment?->teacher?->name ?? 'N/A';
            $location = $schedule->room?->name ?? 'N/A'; // (Đã đúng)
            $status = 'Sắp diễn ra'; // (Placeholder)

            return [
                'id' => $schedule->id,
                'time_range' => $schedule->session,
                'lessons' => $schedule->session,
                'title' => $courseName,
                'course_code' => $classCode, // (Flutter model cho SV có thể dùng key này)
                'location' => $location, // (Đã đúng)
                'status' => $status,
                'teacher_name' => $teacherName,
                'schedule_date' => $schedule->date->toIso8601String(),
            ];
        });
        return response()->json($formattedSchedules);
    }
}