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
}