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
    // 👇 ================== PHẦN ĐÃ SỬA ĐỔI ================== 👇

    /**
     * @OA\Get(
     * path="/api/users",
     * operationId="getUsersList",
     * tags={"Users (CRUD)"},
     * summary="Lấy danh sách người dùng (tìm kiếm, sắp xếp và phân trang 10)",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="page",
     * in="query",
     * required=false,
     * description="Số trang cần lấy",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Parameter(
     * name="name",
     * in="query",
     * required=false,
     * description="Tên người dùng cần tìm kiếm",
     * @OA\Schema(type="string")
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công",
     * @OA\JsonContent()
     * )
     * )
     */
    public function index(Request $request) // 👈 SỬA 1: Thêm Request $request
    {
        // SỬA 2: Lấy từ khóa tìm kiếm
        $searchQuery = $request->input('name');

        // SỬA 3: Bắt đầu xây dựng câu truy vấn
        $query = User::query();

        // SỬA 4: Thêm điều kiện lọc 'where' NẾU có từ khóa tìm kiếm
        $query->when($searchQuery, function ($q) use ($searchQuery) {
            // Sử dụng "where" với "like" để tìm kiếm gần đúng
            return $q->where('name', 'like', '%' . $searchQuery . '%');
        });

        // SỬA 5: Sắp xếp và phân trang (dùng 10 cho nhất quán với UI)
        $users = $query->latest()->paginate(10);

        return response()->json($users, 200);
    }

    // 👆 ================== KẾT THÚC PHẦN SỬA ĐỔI ================== 👆


    /**
     * @OA\Post(
     * path="/api/users",
     * operationId="storeUser",
     * tags={"Users (CRUD)"},
     * summary="Tạo mới người dùng",
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name", "first_name", "last_name", "email", "password", "phone_number", "role", "status"},
     * @OA\Property(property="name", type="string", example="Nguyễn Văn A"),
     * @OA\Property(property="first_name", type="string", example="Nguyễn Văn"),
     * @OA\Property(property="last_name", type="string", example="A"),
     * @OA\Property(property="email", type="string", format="email", example="a@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="password123"),
     * @OA\Property(property="phone_number", type="string", example="0905123456"),
     * @OA\Property(property="role", type="string", enum={"student", "teacher", "training_office", "head_of_department"}),
     * @OA\Property(property="status", type="string", enum={"active", "inactive", "banned"})
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Tạo tài khoản thành công",
     * @OA\JsonContent()
     * ),
     * @OA\Response(
     * response=422,
     * description="Dữ liệu không hợp lệ (Validation error)"
     * )
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
     * operationId="getUserById",
     * tags={"Users (CRUD)"},
     * summary="Xem thông tin 1 người dùng",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="user",
     * in="path",
     * required=true,
     * description="ID của người dùng",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công",
     * @OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy người dùng"
     * )
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
     * operationId="updateUser",
     * tags={"Users (CRUD)"},
     * summary="Cập nhật thông tin người dùng (Gửi bằng PUT)",
     * description="Lưu ý: Route list của bạn dùng PUT|PATCH, nhưng form data chỉ hỗ trợ POST. Nếu dùng Postman, hãy chọn PUT. Nếu dùng form HTML, bạn phải dùng POST và thêm `_method: 'PUT'` vào body.",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="user",
     * in="path",
     * required=true,
     * description="ID của người dùng",
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=false,
     * description="Gửi các trường cần cập nhật. Nếu dùng `application/x-www-form-urlencoded` thì phải thêm `_method: 'PUT'`",
     * @OA\MediaType(
     * mediaType="application/json",
     * @OA\Schema(
     * @OA\Property(property="name", type="string", example="Nguyễn Văn B"),
     * @OA\Property(property="email", type="string", format="email", example="b@example.com"),
     * @OA\Property(property="status", type="string", enum={"active", "inactive"})
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Cập nhật thông tin thành công",
     * @OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy người dùng"
     * ),
     * @OA\Response(
     * response=422,
     * description="Dữ liệu không hợp lệ"
     * )
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
     * operationId="deleteUser",
     * tags={"Users (CRUD)"},
     * summary="Xóa người dùng",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="user",
     * in="path",
     * required=true,
     * description="ID của người dùng",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Xóa người dùng thành công"
     * ),
     * @OA\Response(
     * response=404,
     * description="Không tìm thấy người dùng"
     * )
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
     * operationId="getTeacherHomeSummary",
     * tags={"Teachers (User)"},
     * summary="Lấy tóm tắt trang chủ cho GIÁO VIÊN",
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
     * @OA\JsonContent()
     * )
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
    
    /**
     * @OA\Get(
     * path="/api/users/{user}/schedule-data",
     * operationId="getTeacherScheduleData",
     * tags={"Teachers (User)"},
     * summary="Lấy dữ liệu lịch dạy (hôm nay, tuần) cho GIÁO VIÊN",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="user",
     * in="path",
     * required=true,
     * description="ID của giáo viên",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Parameter(
     * name="week_offset",
     * in="query",
     * required=false,
     * description="Offset tuần (0 = tuần này, 1 = tuần sau, -1 = tuần trước)",
     * @OA\Schema(type="integer", default=0)
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công",
     * @OA\JsonContent()
     * )
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

    // --- <<< SỬA: ĐÃ XÓA HÀM getReportData() KHỎI ĐÂY (ĐÃ CHUYỂN SANG ReportController) >>> ---
    
    // --- API CHO NGHỈ/BÙ GIÁO VIÊN ---

    /**
     * @OA\Get(
     * path="/api/users/{user}/leave-makeup-summary",
     * operationId="getTeacherLeaveMakeupSummary",
     * tags={"Teachers (User)"},
     * summary="Lấy tóm tắt số buổi nghỉ/chờ bù (giáo viên)",
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
     * @OA\JsonContent()
     * )
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
     * operationId="getTeacherPendingMakeupSchedules",
     * tags={"Teachers (User)"},
     * summary="Lấy danh sách lịch nghỉ CHỜ dạy bù (giáo viên)",
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
     * @OA\JsonContent()
     * )
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

    /**
     * @OA\Get(
     * path="/api/students/{user}/home-summary",
     * operationId="getStudentHomeSummary",
     * tags={"Students (User)"},
     * summary="Lấy tóm tắt trang chủ cho SINH VIÊN",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="user",
     * in="path",
     * required=true,
     * description="ID của sinh viên",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công",
     * @OA\JsonContent()
     * ),
     * @OA\Response(
     * response=403,
     * description="Không phải sinh viên"
     * )
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

    // <<< SỬA: XÓA HÀM formatSchedulesForReport KHỎI CONTROLLER NÀY

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
     * operationId="getStudentWeeklySchedule",
     * tags={"Students (User)"},
     * summary="Lấy lịch học CẢ TUẦN cho SINH VIÊN",
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="user",
     * in="path",
     * required=true,
     * description="ID của sinh viên",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Thành công (Trả về 1 danh sách các lịch học)",
     * @OA\JsonContent()
     * ),
     * @OA\Response(
     * response=403,
     * description="Không phải sinh viên / Không có quyền"
     * )
     * )
     */
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
            ->orderBy('date', 'asc')       // Sắp xếp theo ngày
            ->orderBy('session', 'asc')   // Rồi sắp xếp theo tiết
            ->get();

        // 4. Định dạng lại dữ liệu (Quan trọng: phải có 'schedule_date')
        $formattedSchedules = $allWeekSchedules->map(function ($schedule) {
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