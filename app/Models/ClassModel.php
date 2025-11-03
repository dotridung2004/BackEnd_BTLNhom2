<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    /**
     * Chỉ định rõ ràng cho Eloquent biết
     * model này sử dụng bảng 'classes' (Lớp Sinh Viên)
     */
    protected $table = 'classes'; 

    /**
     * Cập nhật 'name' thành 'class_code'
     */
    protected $fillable = [
        'name', // <-- Đã sửa
        'semester',
        'department_id',
    ];

    public function department(){
        return $this->belongsTo(Department::class);
    }

    /**
     * Mối quan hệ với các lớp học phần được gán cho lớp sinh viên này.
     */
    public function classCourseAssignments(){ 
        return $this->hasMany(ClassCourseAssignment::class, 'class_id');
    }
    
    /**
     * Mối quan hệ với sinh viên thuộc lớp sinh viên này
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'class_student', 'class_model_id', 'student_id')
                    ->where('role', 'student')
                    ->withTimestamps();
    }
}