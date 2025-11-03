<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Imports từ cả 2 file, đã gộp và loại bỏ trùng lặp
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
use App\Http\Controllers\Api\MajorController;
use App\Http\Controllers\Api\DivisionController;
use App\Http\Controllers\Api\LecturerController;
use App\Http\Controllers\Api\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Đây là file gộp, bao gồm:
| 1. Route 'login' công khai.
| 2. Nhóm route được bảo vệ bằng 'auth:sanctum'.
| 3. Sắp xếp lại các route cụ thể (vd: /form-data) LÊN TRÊN 'apiResource'.
| 4. Giữ lại các route cho Admin, Giảng viên, Sinh viên.
| 5. Giữ lại sửa lỗi trỏ 'report-data' đến 'ReportController'.
|
*/

// --- Route CÔNG KHAI (Public) ---
// Route đăng nhập, nằm ngoài nhóm bảo vệ
Route::post('login', [AuthController::class, 'login']);


// --- Route ĐƯỢC BẢO VỆ (Cần xác thực) ---
// Tất cả các route bên trong nhóm này đều yêu cầu xác thực
Route::middleware('auth:sanctum')->group(function () {

    // Route cơ bản để lấy thông tin user đã xác thực
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // === CÁC ROUTE CỤ THỂ (PHẢI ĐẶT TRƯỚC API RESOURCES) ===
    // (Giữ lại từ File 1)
    Route::get('/departments/{id}/details', [DepartmentController::class, 'getDetails']);

    // (Giữ lại thứ tự đúng từ File 1)
    Route::get('/class-courses/form-data', [ClassCourseAssignmentController::class, 'getFormData']);
    Route::get('/class-courses/{id}/details', [ClassCourseAssignmentController::class, 'showDetails']);
    Route::get('/registered-courses', [ClassCourseAssignmentController::class, 'indexWithStudentCount']);

    // === CÁC ROUTE TÀI NGUYÊN (API RESOURCES) ===
    // Tự động tạo các route CRUD (GET, POST, PUT/PATCH, DELETE)
    Route::apiResource('users', UserController::class);
    Route::apiResource('attendances', AttendanceController::class);
    Route::apiResource('classmodels', ClassModelController::class);
    Route::apiResource('courses', CourseController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('class-courses', ClassCourseAssignmentController::class); // Đã sửa tên từ 'classcourseassignments' về 'class-courses' cho nhất quán
    Route::apiResource('leave-requests', LeaveRequestController::class);
    Route::apiResource('makeupclasses', MakeupClassController::class);
    Route::apiResource('rooms', RoomController::class);
    Route::apiResource('schedules', ScheduleController::class);
    Route::apiResource('majors', MajorController::class);           // (Thêm từ File 1)
    Route::apiResource('divisions', DivisionController::class);     // (Thêm từ File 1)
    Route::apiResource('lecturers', LecturerController::class);     // (Thêm từ File 2)


    // === CÁC ROUTE TÙY CHỈNH (CUSTOM ROUTES) ===

    // --- Routes cho Giảng viên ---
    Route::get('/users/{user}/home-summary', [UserController::class, 'getHomeSummary']);
    Route::get('/users/{user}/schedule-data', [UserController::class, 'getScheduleData']);
    // (Giữ lại sửa lỗi từ File 2, trỏ đến ReportController)
    Route::get('/users/{user}/report-data', [ReportController::class, 'getReportData']);
    Route::get('/users/{user}/schedules-by-date', [ScheduleController::class, 'getSchedulesByDateForTeacher']);
    Route::get('/users/{user}/leave-makeup-summary', [UserController::class, 'getLeaveMakeupSummary']);
    Route::get('/users/{user}/pending-makeup', [UserController::class, 'getPendingMakeupSchedules']);
    Route::get('/users/{user}/leave-history', [LeaveRequestController::class, 'getLeaveHistoryForTeacher']);
    Route::get('/users/{user}/available-schedules-for-leave', [ScheduleController::class, 'getAvailableSchedulesForLeave']);

    // --- Routes cho Sinh viên ---
    Route::get('/students/{user}/home-summary', [UserController::class, 'getStudentHomeSummary']);
    Route::get('/students/{user}/schedule/week', [UserController::class, 'getStudentWeeklySchedule']);

    // --- Routes cho Điểm danh & Lịch dạy ---
    Route::get('/schedules/{schedule}/students-attendance', [AttendanceController::class, 'getStudentsAndAttendance']);
    Route::post('/attendances/bulk-save', [AttendanceController::class, 'saveBulkAttendance']);

    // --- Routes cho Admin (Quản lý đơn) ---
    // (Giữ lại từ File 2)
    Route::prefix('admin')->group(function () {
        Route::get('/leave-requests/pending', [LeaveRequestController::class, 'getPendingRequests']);
        Route::post('/leave-requests/{id}/approve', [LeaveRequestController::class, 'approveRequest']);
        Route::post('/leave-requests/{id}/reject', [LeaveRequestController::class, 'rejectRequest']);
    });

    /*
    LƯU Ý:
    Các route:
    Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
    Route::post('/makeup-classes', [MakeupClassController::class, 'store']);
    ...đã bị loại bỏ vì chúng BỊ TRÙNG LẶP.
    Route::apiResource('leaverequests', ...) và Route::apiResource('makeupclasses', ...)
    đã tự động tạo các route POST này (trỏ đến phương thức 'store') rồi.
    */

});