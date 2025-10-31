<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course; // 👈 1. THÊM IMPORT MODEL

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 2. THÊM LOGIC LẤY DỮ LIỆU
        // Lấy tất cả các học phần, đồng thời tải
        // thông tin 'department' (khoa) liên quan
        $courses = Course::with('department')->get();

        // 3. Trả về dữ liệu dưới dạng JSON
        return response()->json($courses);
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