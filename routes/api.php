<?php 
// routes/api.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
// Tự động tạo các route cho CRUD: GET, POST, PUT, DELETE,...
Route::apiResource('users', ProductController::class);
Route::apiResource('attendances', CategoryController::class);