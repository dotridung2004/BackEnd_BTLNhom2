<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Nên thêm HasFactory

class ClassCourseAssignment extends Model
{
    use HasFactory; // Nên thêm

    protected $fillable = [
        'class_id',
        'course_id',
        'teacher_id',
    ];

    public function course(){
        return $this->belongsTo(Course::class);
    }

    // --- CHỈ GIỮ LẠI MỘT HÀM NÀY ---
    public function teacher(){
        return $this->belongsTo(User::class,'teacher_id');
    }
    // --- HÀM BỊ TRÙNG LẶP ĐÃ BỊ XÓA ---

    public function schedules(){
        return $this->hasMany(Schedule::class, 'class_course_assignment_id'); // Thêm khóa ngoại cho rõ ràng
    }

    public function classModel()
    {
        return $this->belongsTo(ClassModel::class, 'class_id'); 
    }
}