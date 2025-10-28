<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// 👈 1. THÊM IMPORT MODEL
use App\Models\ClassCourseAssignment; 

class ClassCourseAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 2. THÊM LOGIC LẤY DỮ LIỆU
        // Lấy tất cả các lớp học phần, đồng thời tải
        // các thông tin liên quan (lồng nhau)
        $assignments = ClassCourseAssignment::with([
            'teacher', // Tải thông tin Giảng viên
            'course',  // Tải thông tin Học phần
            'course.department' // Tải thông tin Khoa (từ Học phần)
        ])->get();

        // 3. Trả về dữ liệu dưới dạng JSON
        return response()->json($assignments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // (Bạn sẽ thêm logic 'Thêm mới' ở đây sau)
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // (Bạn sẽ thêm logic 'Cập nhật' ở đây sau)
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // (Bạn sẽ thêm logic 'Xóa' ở đây sau)
    }
}