<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassCourseAssignment extends Model
{
    protected $fillable = [
        'class_id',
        'course_id',
        'teacher_id',
    ];
    public function course(){
        return $this->belongsTo(Course::class);
    }
    public function teacher(){
        return $this->belongsTo(User::class,'teacher_id');
    }
    public function schedules(){
        return $this->hasMany(Schedule::class);
    }
    public function classModel()
    {
        // 'class_id' trỏ đến bảng 'classes', 
        // ⚠️ Đảm bảo bạn có Model 'ClassModel' (hoặc tên tương tự)
        return $this->belongsTo(ClassModel::class, 'class_id'); 
    }
}
