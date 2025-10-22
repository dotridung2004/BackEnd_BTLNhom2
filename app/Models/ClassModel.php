<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // 👈 THÊM DÒNG NÀY
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory; // 👈 THÊM DÒNG NÀY

    /**
     * 💡 SỬA LỖI:
     * Chỉ định rõ ràng cho Eloquent biết
     * model này sử dụng bảng 'classes'
     */
    protected $table = 'classes'; // 👈 THÊM DÒNG NÀY

    protected $fillable = [
        'name',
        'semester',
        'academic_year',
        'department_id',
    ];

    public function department(){
        return $this->belongsTo(Department::class);
    }

    // Đổi tên hàm cho đúng quy tắc (tùy chọn nhưng nên làm)
    public function classCourseAssignments(){ // 👈 Sửa 'Assignment' thành 'Assignments' (số nhiều)
        return $this->hasMany(ClassCourseAssignment::class,'class_id');
    }
    public function students()
    {
        // belongsToMany(Model liên quan, 'tên_bảng_trung_gian', 'khóa_ngoại_của_model_này', 'khóa_ngoại_của_model_liên_quan')
        return $this->belongsToMany(User::class, 'class_student', 'class_model_id', 'student_id')
                    ->where('role', 'student') // Chỉ lấy những user có vai trò là 'student'
                    ->withTimestamps(); // Nếu bảng trung gian của bạn có timestamps (created_at, updated_at)
    }
}