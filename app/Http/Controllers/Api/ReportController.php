<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\MakeupClass;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function getOverallReport(Request $request)
    {
        $filters = [
            'semester' => $request->input('semester'),
            'department_id' => $request->input('department_id'),
        ];

        $overviewData = $this->getOverviewData($filters);
        $teachingHoursData = $this->getTeachingHoursData($filters);
        $attendanceData = $this->getAttendanceData($filters);

        return response()->json([
            'overview' => $overviewData,
            'teaching_hours' => $teachingHoursData,
            'attendance' => $attendanceData,
        ]);
    }

    private function getOverviewData($filters)
    {
        // Đảm bảo luôn là số nguyên
        $totalSessions = (int) Schedule::count();
        $lecturerCount = (int) User::where('role', 'teacher')->count();
        
        $taughtSessions = (int) Schedule::where('status', 'taught')->count();
        $totalSchedAndTaught = (int) Schedule::whereIn('status', ['scheduled', 'taught'])->count();
        
        // Kiểm tra chia cho 0
        $completionRate = ($totalSchedAndTaught > 0) ? ($taughtSessions / $totalSchedAndTaught) * 100 : 0.0;

        $leaveAndMakeupCount = (int) (LeaveRequest::where('status', 'approved')->count() + MakeupClass::where('status', 'approved')->count());

        $monthlyHours = Schedule::select(
                DB::raw('MONTH(date) as month_num'),
                DB::raw("SUM(CASE WHEN status IN ('scheduled', 'taught') THEN 1 ELSE 0 END) as planned"),
                DB::raw("SUM(CASE WHEN status = 'taught' THEN 1 ELSE 0 END) as actual"),
                DB::raw("SUM(CASE WHEN status = 'makeup' THEN 1 ELSE 0 END) as makeup")
            )
            ->groupBy('month_num')
            ->orderBy('month_num', 'asc')
            ->get()
            ->map(function($item) {
                return [
                    'month' => 'T' . $item->month_num,
                    'planned' => (int) ($item->planned ?? 0),
                    'actual' => (int) ($item->actual ?? 0),
                    'makeup' => (int) ($item->makeup ?? 0),
                ];
            });

        $attendanceStats = Attendance::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');
        
        $totalAttendance = (int) $attendanceStats->sum();
        
        // Kiểm tra chia cho 0 cho từng phần trăm
        $presentPercent = ($totalAttendance > 0) ? ($attendanceStats->get('present', 0) / $totalAttendance) * 100 : 0.0;
        $excusedAbsencePercent = ($totalAttendance > 0) ? ($attendanceStats->get('absent', 0) / $totalAttendance) * 100 : 0.0;
        $unexcusedAbsencePercent = ($totalAttendance > 0) ? ($attendanceStats->get('late', 0) / $totalAttendance) * 100 : 0.0;
        
        $departmentProgress = Department::withCount([
                'courses as total_sessions' => function ($query) {
                    $query->select(DB::raw('count(schedules.id)'))
                        ->join('class_course_assignments', 'courses.id', '=', 'class_course_assignments.course_id')
                        ->join('schedules', 'class_course_assignments.id', '=', 'schedules.class_course_assignment_id')
                        ->whereIn('schedules.status', ['scheduled', 'taught']);
                },
                'courses as actual_sessions' => function ($query) {
                    $query->select(DB::raw('count(schedules.id)'))
                        ->join('class_course_assignments', 'courses.id', '=', 'class_course_assignments.course_id')
                        ->join('schedules', 'class_course_assignments.id', '=', 'schedules.class_course_assignment_id')
                        ->where('schedules.status', 'taught');
                }
            ])
            ->get()
            ->map(function($dept) {
                // Đảm bảo giá trị là số nguyên và không null
                $actual = (int) ($dept->actual_sessions ?? 0);
                $total = (int) ($dept->total_sessions ?? 0);

                return [
                    'name' => $dept->name,
                    'actual' => $actual,
                    'total' => $total,
                ];
            });

        return [
            'kpi_cards' => [
                'total_hours' => ['value' => $totalSessions, 'change' => 12],
                'lecturer_count' => ['value' => $lecturerCount, 'change' => 5],
                'completion_rate' => ['value' => round($completionRate, 1), 'change' => 3],
                'leave_makeup_sessions' => ['value' => $leaveAndMakeupCount, 'change' => -8],
            ],
            'monthly_hours_chart' => $monthlyHours,
            'attendance_pie_chart' => [
                'present' => round($presentPercent, 1),
                'excused_absence' => round($excusedAbsencePercent, 1),
                'unexcused_absence' => round($unexcusedAbsencePercent, 1),
            ],
            'department_progress' => $departmentProgress
        ];
    }

    private function getTeachingHoursData($filters)
    {
        $topLecturers = User::where('role', 'teacher')
            ->with('department')
            ->withCount(['taughtSchedules as total_hours'])
            ->withCount(['classCourseAssignments as class_count'])
            ->orderBy('total_hours', 'desc')
            ->limit(5)
            ->get()
            ->map(function($lecturer, $index) {
                return [
                    'rank' => $index + 1,
                    'name' => $lecturer->name,
                    'department' => $lecturer->department->name ?? 'N/A',
                    'class_count' => (int) ($lecturer->class_count ?? 0),
                    'total_hours' => (int) ($lecturer->total_hours ?? 0),
                    'status' => ((int)($lecturer->total_hours ?? 0) > 50) ? 'Đạt' : 'Chưa đạt',
                ];
            });
            
        $hoursTrend = Schedule::select(
                DB::raw('MONTH(date) as month_num'),
                DB::raw("COUNT(*) as planned"),
                DB::raw("SUM(CASE WHEN status = 'taught' THEN 1 ELSE 0 END) as actual")
            )
            ->groupBy('month_num')
            ->orderBy('month_num', 'asc')
            ->get()
            ->map(function($item) {
                return [
                    'month' => 'T' . $item->month_num,
                    'planned' => (int) ($item->planned ?? 0),
                    'actual' => (int) ($item->actual ?? 0),
                ];
            });

        return [
            'top_lecturers' => $topLecturers,
            'hours_trend_chart' => $hoursTrend,
        ];
    }

    private function getAttendanceData($filters)
    {
        $attendanceStats = Attendance::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalAttendance = (int) $attendanceStats->sum(); // Ép kiểu thành int
        $presentCount = (int) $attendanceStats->get('present', 0);
        $absentCount = (int) $attendanceStats->get('absent', 0);
        $lateCount = (int) $attendanceStats->get('late', 0);
        
        $attendanceByDept = Department::all()->map(function($dept) use ($filters) {
            $query = Attendance::join('schedules', 'attendances.schedule_id', '=', 'schedules.id')
                ->join('class_course_assignments', 'schedules.class_course_assignment_id', '=', 'class_course_assignments.id')
                ->join('courses', 'class_course_assignments.course_id', '=', 'courses.id')
                ->where('courses.department_id', $dept->id);

            // Áp dụng bộ lọc nếu có
            if (!empty($filters['semester'])) {
                $query->where('class_course_assignments.semester', $filters['semester']);
            }
            // Không áp dụng department_id ở đây vì chúng ta đang lặp qua từng phòng ban

            $stats = $query->select('attendances.status', DB::raw('count(attendances.id) as count'))
                ->groupBy('attendances.status')
                ->pluck('count', 'status');

            $total = (int) $stats->sum(); // Ép kiểu thành int
            
            // Đảm bảo chia cho 0 được xử lý
            $present = ($total > 0) ? round(($stats->get('present', 0) / $total) * 100) : 0.0;
            $excused = ($total > 0) ? round(($stats->get('absent', 0) / $total) * 100) : 0.0;
            $unexcused = ($total > 0) ? round(($stats->get('late', 0) / $total) * 100) : 0.0;

            return [
                'name' => $dept->name,
                'present' => $present,
                'excused' => $excused,
                'unexcused' => $unexcused,
            ];
        });

        return [
            'summary' => [
                'present' => ['value' => $presentCount, 'percentage' => ($totalAttendance > 0) ? round(($presentCount / $totalAttendance) * 100, 1) : 0.0],
                'excused_absence' => ['value' => $absentCount, 'percentage' => ($totalAttendance > 0) ? round(($absentCount / $totalAttendance) * 100, 1) : 0.0],
                'unexcused_absence' => ['value' => $lateCount, 'percentage' => ($totalAttendance > 0) ? round(($lateCount / $totalAttendance) * 100, 1) : 0.0],
            ],
            'by_department' => $attendanceByDept,
        ];
    }
}