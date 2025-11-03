<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// 👇 THÊM CÁC IMPORT NÀY
use App\Models\Course;
use App\Models\User;
use App\Models\Schedule;
use App\Models\ClassModel; // Đảm bảo bạn có Model tên là ClassModel

class ClassCourseAssignment extends Model
{
    use HasFactory;

    // Chỉ định tên bảng nếu tên Model không khớp (ví dụ: ClassCourseAssignments)
    // Nếu tên bảng của bạn là 'class_course_assignments' (số nhiều) thì dòng này không cần thiết.
    // protected $table = 'class_course_assignments'; 

    protected $fillable = [
    'class_id',
    'course_id',
    'teacher_id',
        'semester', // Migration của bạn có trường 'semester', có thể bạn cũng muốn thêm vào đây
    ];

    /**
     * Lấy môn học của phân công này.
     */
     public function course()
     {
        // Giả định khóa ngoại là 'course_id' (đúng theo migration)
     return $this->belongsTo(Course::class);
     }

    /**
     * Lấy giáo viên của phân công này.
     */
    public function teacher()
    {
    return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Lấy tất cả lịch học (schedules) thuộc phân công này.
     */
    public function schedules()
    {
    return $this->hasMany(Schedule::class, 'class_course_assignment_id');
    }

    /**
     * Lấy lớp học (class) của phân công này.
     */
    public function classModel()
    {
    return $this->belongsTo(ClassModel::class, 'class_id');
    }
}