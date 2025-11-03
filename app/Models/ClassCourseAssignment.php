<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// Import từ cả hai file
use App\Models\Course;
use App\Models\User;
use App\Models\Schedule;
use App\Models\ClassModel;
use App\Models\Division; // Thêm từ file 1
use App\Models\Room;     // Thêm từ file 1

class ClassCourseAssignment extends Model
{
    use HasFactory;

    /**
     * Chỉ định rõ ràng bảng
     */
    protected $table = 'class_course_assignments';

    /**
     * Các thuộc tính có thể gán hàng loạt (đã gộp từ cả 2 file)
     */
    protected $fillable = [
        'class_id',
        'course_id',
        'teacher_id',
        'division_id', // Từ file 1
        'room_id',     // Từ file 1
        'semester',    // Từ file 2
    ];

    /**
     * Lấy môn học của phân công này.
     */
    public function course()
    {
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
     * Mối quan hệ với Bộ môn (Division)
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Mối quan hệ với Phòng học (Room)
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    /**
     * Lấy tất cả lịch học (schedules) thuộc phân công này.
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'class_course_assignment_id');
    }

    /**
     * Mối quan hệ để lấy Lớp Sinh Viên (ví dụ: 65CNTT1)
     */
    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Mối quan hệ lấy sinh viên (giữ nguyên từ file 1)
     */
    public function students()
    {
        return $this->belongsToMany(
            User::class,        // Model Sinh viên (User)
            'class_student',    // Bảng trung gian
            'class_model_id',   // Khóa ngoại trên bảng trung gian (trỏ đến ClassModel)
            'student_id',       // Khóa ngoại trên bảng trung gian (trỏ đến User)
            'class_id',         // Khóa cục bộ trên bảng này (ClassCourseAssignment)
            'id'                // Khóa liên quan trên ClassModel
        )->where('role', 'student'); // Lọc chỉ lấy sinh viên
    }
}