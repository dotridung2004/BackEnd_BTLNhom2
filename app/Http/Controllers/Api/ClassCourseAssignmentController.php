<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassCourseAssignment; // Đã import model

class ClassCourseAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     * (Hàm này lấy từ file thứ 2 - đã format cho Flutter)
     */
    public function index()
    {
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
     * Display a listing of the resource with student count.
     * (Hàm này lấy từ file thứ 1 - dùng cho màn hình "Học phần đã đăng ký")
     */
    public function indexWithStudentCount()
    {
        // 'withCount('students')' sẽ tự động thêm cột 'students_count'
        // Đảm bảo bạn có quan hệ tên 'students' trong Model ClassCourseAssignment
        $assignments = ClassCourseAssignment::with([
            'teacher',
            'course'
        ])
        ->withCount('students') 
        ->get();

        return response()->json($assignments);
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