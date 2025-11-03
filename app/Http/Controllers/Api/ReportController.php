<?php

// <<< SỬA 1: THÊM \Api VÀO NAMESPACE
namespace App\Http\Controllers\Api;

// <<< SỬA 2: THÊM "use" CHO CÁC CLASS CẦN THIẾT
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Lấy dữ liệu báo cáo tổng hợp cho giáo viên
     */
    public function getReportData(Request $request, $userId)
    {
        // 1. Validate input
        $validated = $request->validate([
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        // 2. Lấy Query cơ sở
        $baseSchedulesQuery = Schedule::whereHas('classCourseAssignment', function ($query) use ($userId) {
            $query->where('teacher_id', $userId);
        })
        ->whereBetween('date', [$startDate, $endDate])
        ->with([ 
            'classCourseAssignment:id,class_id,course_id', 
            'classCourseAssignment.course:id,name,code',
            'classCourseAssignment.classModel:id', 
            'room:id,name',
        ]);

        // 3. Lấy dữ liệu chi tiết
        $detailedSchedules = $baseSchedulesQuery->clone()
            ->orderBy('date', 'asc')
            ->get();

        // Nếu không có lịch nào, trả về rỗng
        if ($detailedSchedules->isEmpty()) {
            return response()->json([
                'summary' => ['totalSessions' => 0, 'absencesCount' => 0, 'makeupsCount' => 0, 'attendanceRate' => 0.0],
                'chartData' => [],
                'details' => [],
            ]);
        }

        // 4. Lấy dữ liệu Sĩ số và Điểm danh
        $scheduleIds = $detailedSchedules->pluck('id');
        $classIds = $detailedSchedules->map(function ($schedule) {
            return data_get($schedule, 'classCourseAssignment.classModel.id');
        })->whereNotNull()->unique();

        // [class_id => count]
        $studentCounts = DB::table('class_student')
            ->whereIn('class_model_id', $classIds)
            ->groupBy('class_model_id')
            ->select('class_model_id', DB::raw('count(*) as count'))
            ->pluck('count', 'class_model_id');
            
        // [schedule_id => count]
        $presentCounts = Attendance::whereIn('schedule_id', $scheduleIds)
            ->where('status', 'present')
            ->groupBy('schedule_id')
            ->select('schedule_id', DB::raw('count(*) as count'))
            ->pluck('count', 'schedule_id');


        // 5. TÍNH TOÁN SUMMARY
        $totalSessions = $detailedSchedules->count();
        $makeupsCount = $detailedSchedules->where('status', 'makeup')->count();
        
        // <<< SỬA 3: SỬA LỖI LOGIC TÍNH CHUYÊN CẦN >>>
        // Phải bao gồm cả các buổi 'scheduled' (đã lên lịch)
        // vì chúng cũng là các buổi học cần được điểm danh.
        $taughtSchedules = $detailedSchedules->whereIn('status', ['taught', 'makeup', 'scheduled']);
        
        $totalPossibleAttendances = 0;
        $totalPresents = 0;

        foreach ($taughtSchedules as $schedule) {
            $classId = data_get($schedule, 'classCourseAssignment.classModel.id');
            if ($classId) {
                // Lấy sĩ số của lớp
                $totalStudentsInClass = $studentCounts->get($classId, 0);
                
                // Chỉ tính toán nếu lớp có sinh viên
                if ($totalStudentsInClass > 0) {
                    $totalPossibleAttendances += $totalStudentsInClass;
                    $totalPresents += $presentCounts->get($schedule->id, 0);
                }
            }
        }
        
        $attendanceRate = ($totalPossibleAttendances > 0)
            ? ($totalPresents / $totalPossibleAttendances) * 100
            : 0;

        $absencesCount = LeaveRequest::where('teacher_id', $userId)
            ->where('status', 'approved')
            ->whereHas('schedule', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            })
            ->count();

        $summary = [
            'totalSessions' => $totalSessions,
            'absencesCount' => $absencesCount,
            'makeupsCount' => $makeupsCount,
            'attendanceRate' => (double) number_format($attendanceRate, 1),
        ];

        // 6. CHUẨN BỊ DỮ LIỆU CHART
        $chartData = [
            ['label' => 'Tổng buổi', 'value' => $totalSessions],
            ['label' => 'Nghỉ', 'value' => $absencesCount],
            ['label' => 'Dạy bù', 'value' => $makeupsCount],
        ];

        // 7. CHUẨN BỊ DỮ LIỆU CHI TIẾT (DETAILS)
        $details = $detailedSchedules->map(function ($schedule) use ($studentCounts, $presentCounts) {
            
            $course = data_get($schedule, 'classCourseAssignment.course');
            $classId = data_get($schedule, 'classCourseAssignment.classModel.id');

            $courseName = $course->name ?? 'N/A';
            $courseCode = $course->code ?? 'N/A';
            
            $totalStudents = $studentCounts->get($classId, 0);
            $presentStudents = $presentCounts->get($schedule->id, 0);
            
            $sessionAttendance = ($totalStudents > 0)
                ? number_format(($presentStudents / $totalStudents) * 100, 0) . '%'
                : '0%'; // Nếu lớp 0 sinh viên thì 0%

            return [
                'dateString' => $schedule->date->format('d/m'),
                'title' => $courseName,
                'courseCode' => '(' . $courseCode . ')',
                'lessons' => $schedule->session,
                'location' => $schedule->room->name ?? 'N/A',
                'students' => "$presentStudents/$totalStudents", // 'SV'
                'attendance' => $sessionAttendance, // '%CC'
            ];
        });

        // 8. Trả về JSON cuối cùng
        return response()->json([
            'summary' => $summary,
            'chartData' => $chartData,
            'details' => $details,
        ]);
    }
}