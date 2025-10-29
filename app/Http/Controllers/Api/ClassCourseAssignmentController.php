<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassCourseAssignment;
class ClassCourseAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
        // 👈 THÊM LOGIC NÀY
        
        // 1. Tải tất cả các phân công, cùng với quan hệ
        $assignments = ClassCourseAssignment::with(['teacher', 'course', 'classModel'])
            ->get();

        // 2. Định dạng lại để Flutter dễ hiển thị
        $formatted = $assignments->map(function ($assignment) {
            $teacherName = $assignment->teacher?->name ?? 'N/A';
            $courseName  = $assignment->course?->name ?? 'N/A';
            $classCode   = $assignment->classModel?->name ?? 'N/A';

            return [
                'id' => $assignment->id, // ID này là thứ chúng ta cần lưu
                
                // Tên hiển thị trong dropdown của Flutter
                'display_name' => "GV: {$teacherName} | Môn: {$courseName} | Lớp: {$classCode}",
                
                // Gửi thêm 3 thông tin riêng lẻ để Flutter dùng cho việc "Sửa"
                'teacherName' => $teacherName,
                'courseName'  => $courseName,
                'classCode'   => $classCode,
                'semester'    => $assignment->semester ?? 'N/A', // Giả sử học kỳ nằm ở đây
            ];
        });

        return response()->json($formatted);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
