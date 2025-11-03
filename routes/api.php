<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
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
use App\Http\Controllers\Api\ReportController; // <<< 1. THÊM REPORT CONTROLLER

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Enjoy building your API!
|
*/

// === ROUTE CÔNG KHAI ===
// Route đăng nhập không cần xác thực
Route::post('login', [AuthController::class, 'login']);


// === CÁC ROUTE CẦN XÁC THỰC (Phải gửi kèm Token) ===
Route::middleware('auth:sanctum')->group(function () {

    // Tự động tạo các route cho CRUD: GET, POST, PUT, DELETE,...
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

    // === API BÁO CÁO (MỚI THÊM) ===
    Route::get('/reports/overview', [ReportController::class, 'getOverallReport']);


    // === CÁC API CUSTOM CHO USER (GIÁO VIÊN) ===
    Route::get('/users/{user}/home-summary', [UserController::class, 'getHomeSummary']);
    Route::get('/users/{user}/schedule-data', [UserController::class, 'getScheduleData']);
    Route::get('/users/{user}/report-data', [UserController::class, 'getReportData']); // (API này có thể gộp vào ReportController)
    Route::get('/users/{user}/schedules-by-date', [ScheduleController::class, 'getSchedulesByDateForTeacher']);
    Route::get('/users/{user}/leave-makeup-summary', [UserController::class, 'getLeaveMakeupSummary']);
    Route::get('/users/{user}/pending-makeup', [UserController::class, 'getPendingMakeupSchedules']);
    Route::get('/users/{user}/leave-history', [LeaveRequestController::class, 'getLeaveHistoryForTeacher']);
    Route::get('/users/{user}/available-schedules-for-leave', [ScheduleController::class, 'getAvailableSchedulesForLeave']);


    // === CÁC API CUSTOM CHO SINH VIÊN ===
    Route::get('/students/{user}/home-summary', [UserController::class, 'getStudentHomeSummary']);
    Route::get('/students/{user}/schedule/week', [UserController::class, 'getStudentWeeklySchedule']);


    // === CÁC API CUSTOM CHUNG ===
    // Lấy danh sách sinh viên + điểm danh cho lịch dạy
    Route::get('/schedules/{schedule}/students-attendance', [AttendanceController::class, 'getStudentsAndAttendance']);
    // Lưu/Cập nhật điểm danh hàng loạt
    Route::post('/attendances/bulk-save', [AttendanceController::class, 'saveBulkAttendance']);
    // Gửi yêu cầu đăng ký nghỉ
    Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
    // Gửi yêu cầu đăng ký dạy bù
    Route::post('/makeup-classes', [MakeupClassController::class, 'store']);

    // Route lấy thông tin user đang đăng nhập (nếu cần)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
