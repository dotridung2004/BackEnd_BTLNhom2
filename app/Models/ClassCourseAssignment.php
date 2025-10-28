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
    public function students()
    {
        // Giả sử bạn có một bảng trung gian là class_student
        // và bảng này liên kết 'class_model_id' với 'student_id' (của sinh viên)
        // Hãy thay 'class_student' và 'student_id' cho đúng với CSDL của bạn
        
        return $this->belongsToMany(
            User::class,            // Model của Sinh viên (giả sử là User)
            'class_student',  // Tên bảng trung gian
            'class_model_id', // Khóa ngoại của model này
            'student_id'               // Khóa ngoại của model sinh viên
        );

        // --- HOẶC ---
        // Nếu bạn có model StudentEnrollment riêng, bạn có thể dùng hasMany:
        // return $this->hasMany(StudentEnrollment::class);
    }
}