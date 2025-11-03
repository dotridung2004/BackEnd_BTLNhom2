<?php 
// routes/api.php
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\ClassCourseAssignmentController;
use App\Http\Controllers\Api\ClassModelController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\MakeupClassController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\LecturerController; 

// <<< SỬA 1: Sửa lại namespace cho đúng
use App\Http\Controllers\Api\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- Route CÔNG KHAI (Public) ---
Route::post('login', [AuthController::class, 'login']);


// --- Route ĐƯỢC BẢO VỆ (Cần xác thực) ---
Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // CRUD Resources
    Route::apiResource('users', UserController::class);
    Route::apiResource('attendances', AttendanceController::class);
    Route::apiResource('classcourseassignments', ClassCourseAssignmentController::class);
    Route::apiResource('classmodels', ClassModelController::class);
    Route::apiResource('courses', CourseController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('leaverequests', LeaveRequestController::class);
    Route::apiResource('makeupclasses', MakeupClassController::class);
    Route::apiResource('rooms', RoomController::class);
    Route::apiResource('schedules', ScheduleController::class);
    Route::apiResource('lecturers', LecturerController::class);

    // --- Routes cho Giảng viên ---
    Route::get('/users/{user}/home-summary', [UserController::class, 'getHomeSummary']);
    Route::get('/users/{user}/schedule-data', [UserController::class, 'getScheduleData']);
    
    // <<< SỬA 2: Trỏ route đến ReportController >>>
    Route::get('/users/{user}/report-data', [ReportController::class, 'getReportData']);

    Route::get('/schedules/{schedule}/students-attendance', [AttendanceController::class, 'getStudentsAndAttendance']);
    Route::post('/attendances/bulk-save', [AttendanceController::class, 'saveBulkAttendance']);
    Route::get('/users/{user}/schedules-by-date', [ScheduleController::class, 'getSchedulesByDateForTeacher']);
    Route::get('/users/{user}/leave-makeup-summary', [UserController::class, 'getLeaveMakeupSummary']);
    Route::get('/users/{user}/pending-makeup', [UserController::class, 'getPendingMakeupSchedules']); 
    Route::get('/users/{user}/leave-history', [LeaveRequestController::class, 'getLeaveHistoryForTeacher']);
    Route::get('/users/{user}/available-schedules-for-leave', [ScheduleController::class, 'getAvailableSchedulesForLeave']);
    Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
    Route::post('/makeup-classes', [MakeupClassController::class, 'store']);

    // --- Routes cho Sinh viên ---
    Route::get('/students/{user}/home-summary', [UserController::class, 'getStudentHomeSummary']);
    Route::get('/students/{user}/schedule/week', [UserController::class, 'getStudentWeeklySchedule']);

    
    // --- ⬇️ THÊM CÁC ROUTE NÀY CHO ADMIN ⬇️ ---
    Route::prefix('admin')->group(function () {
        // Lấy danh sách chờ duyệt
        Route::get('/leave-requests/pending', [LeaveRequestController::class, 'getPendingRequests']);
        // Duyệt đơn
        Route::post('/leave-requests/{id}/approve', [LeaveRequestController::class, 'approveRequest']);
        // Từ chối đơn
        Route::post('/leave-requests/{id}/reject', [LeaveRequestController::class, 'rejectRequest']);
    });
    // --- ⬆️ KẾT THÚC ROUTE ADMIN ⬆️ ---

});