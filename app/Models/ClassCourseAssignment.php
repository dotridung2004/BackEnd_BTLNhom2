<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassCourseAssignment extends Model
{
    use HasFactory;

    /**
     * Chỉ định rõ ràng bảng
     */
    protected $table = 'class_course_assignments';

    /**
     * Các thuộc tính có thể gán hàng loạt
     */
    protected $fillable = [
        'class_id',
        'course_id',
        'teacher_id',
        'division_id',
        'room_id', // <-- Đã có từ lần trước
    ];

    public function course(){
        return $this->belongsTo(Course::class);
    }

    public function teacher(){
        return $this->belongsTo(User::class,'teacher_id');
    }

    /**
     * Mối quan hệ với Bộ môn (Division)
     */
    public function division(){
        return $this->belongsTo(Division::class);
    }

    // ✅ SỬA LỖI: THÊM HÀM NÀY
    /**
     * Mối quan hệ với Phòng học (Room)
     */
    public function room()
    {
        // Giả sử cột khóa ngoại là 'room_id'
        return $this->belongsTo(Room::class, 'room_id'); 
    }
    // ===================================

    public function schedules(){
        return $this->hasMany(Schedule::class, 'class_course_assignment_id');
    }

    /**
     * Mối quan hệ để lấy Lớp Sinh Viên (ví dụ: 65CNTT1)
     */
    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id'); 
    }
    
    // (Giữ nguyên hàm students()... )
    public function students()
    {
        return $this->belongsToMany(
            User::class,          // Model Sinh viên (User)
            'class_student',      // Bảng trung gian
            'class_model_id',     // Khóa ngoại trên bảng trung gian (trỏ đến ClassModel)
            'student_id',         // Khóa ngoại trên bảng trung gian (trỏ đến User)
            'class_id',           // Khóa cục bộ trên bảng này (ClassCourseAssignment)
            'id'                  // Khóa liên quan trên ClassModel
        )->where('role', 'student'); // Lọc chỉ lấy sinh viên
    }
}