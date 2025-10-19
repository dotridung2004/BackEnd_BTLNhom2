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