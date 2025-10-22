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
Route::post('login', [AuthController::class, 'login']);
Route::get('/users/{user}/home-summary', [UserController::class, 'getHomeSummary']);
Route::get('/users/{user}/schedule-data', [UserController::class, 'getScheduleData']);
Route::get('/users/{user}/report-data', [UserController::class, 'getReportData']);
// Lấy danh sách sinh viên + điểm danh cho lịch dạy cụ thể theo ngày
Route::get('/schedules/{schedule}/students-attendance', [AttendanceController::class, 'getStudentsAndAttendance']);
// Lưu/Cập nhật điểm danh hàng loạt
Route::post('/attendances/bulk-save', [AttendanceController::class, 'saveBulkAttendance']);
Route::get('/users/{user}/schedules-by-date', [ScheduleController::class, 'getSchedulesByDateForTeacher']);