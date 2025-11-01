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
use App\Http\Controllers\Api\MajorController;
use App\Http\Controllers\Api\DivisionController;

// Tự động tạo các route cho CRUD: GET, POST, PUT, DELETE,...
Route::apiResource('users', UserController::class);
Route::apiResource('attendances', AttendanceController::class);
Route::apiResource('classmodels', ClassModelController::class);
Route::apiResource('courses', CourseController::class);

// (Đặt route chi tiết LÊN TRÊN route resource)
Route::get('/departments/{id}/details', [DepartmentController::class, 'getDetails']);
Route::apiResource('departments', DepartmentController::class);

// ===== ✅ SỬA LỖI THỨ TỰ TẠI ĐÂY =====
// CÁC ROUTE CỤ THỂ CHO 'class-courses' PHẢI NẰM TRÊN 'apiResource'
Route::get('/class-courses/form-data', [ClassCourseAssignmentController::class, 'getFormData']);
Route::get('/class-courses/{id}/details', [ClassCourseAssignmentController::class, 'showDetails']);
Route::get('/registered-courses', [ClassCourseAssignmentController::class, 'indexWithStudentCount']);

// Route 'apiResource' cho 'class-courses' phải nằm cuối cùng
Route::apiResource('class-courses', ClassCourseAssignmentController::class);
// ====================================

Route::apiResource('leaverequests', LeaveRequestController::class);
Route::apiResource('makeupclasses', MakeupClassController::class);
Route::apiResource('rooms', RoomController::class);
Route::apiResource('schedules', ScheduleController::class);
Route::apiResource('majors', MajorController::class);
Route::apiResource('divisions', DivisionController::class);

Route::post('login', [AuthController::class, 'login']);
Route::get('/users/{user}/home-summary', [UserController::class, 'getHomeSummary']);
Route::get('/users/{user}/schedule-data', [UserController::class, 'getScheduleData']);
Route::get('/users/{user}/report-data', [UserController::class, 'getReportData']);

// Lấy danh sách sinh viên + điểm danh cho lịch dạy cụ thể theo ngày
Route::get('/schedules/{schedule}/students-attendance', [AttendanceController::class, 'getStudentsAndAttendance']);
// Lưu/Cập nhật điểm danh hàng loạt
Route::post('/attendances/bulk-save', [AttendanceController::class, 'saveBulkAttendance']);
Route::get('/users/{user}/schedules-by-date', [ScheduleController::class, 'getSchedulesByDateForTeacher']);
Route::get('/users/{user}/leave-makeup-summary', [UserController::class, 'getLeaveMakeupSummary']);
Route::get('/users/{user}/pending-makeup', [UserController::class, 'getPendingMakeupSchedules']);
Route::get('/users/{user}/leave-history', [LeaveRequestController::class, 'getLeaveHistoryForTeacher']);

// Lấy lịch dạy sắp tới (để chọn khi đăng ký nghỉ)
Route::get('/users/{user}/available-schedules-for-leave', [ScheduleController::class, 'getAvailableSchedulesForLeave']);

// Gửi yêu cầu đăng ký nghỉ
Route::post('/leave-requests', [LeaveRequestController::class, 'store']);

// Gửi yêu cầu đăng ký dạy bù
Route::post('/makeup-classes', [MakeupClassController::class, 'store']);
Route::get('/students/{user}/home-summary', [UserController::class, 'getStudentHomeSummary']);
Route::get('/students/{user}/schedule/week', [UserController::class, 'getStudentWeeklySchedule']);

// DÒNG BỊ TRÙNG LẶP Ở CUỐI ĐÃ BỊ XÓA

