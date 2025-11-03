<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// <<< THÊM CÁC KHAI BÁO (IMPORT) CHO CÁC QUAN HỆ
use App\Models\ClassCourseAssignment;
use App\Models\Department;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\MakeupClass;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Các thuộc tính có thể được gán hàng loạt.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone_number',
        'avatar_url',
        'gender',
        'date_of_birth',
        'role',
        'status',
        // Thêm các trường khác nếu có, ví dụ: department_id
        'department_id',
    ];

    /**
     * Các thuộc tính nên được ẩn khi chuyển thành JSON.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Lấy các thuộc tính nên được ép kiểu.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Nên giữ lại khai báo này cho rõ ràng
    ];

    // --- Các hàm định nghĩa quan hệ (Relationships) ---

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function classCourseAssignments()
    {
        return $this->hasMany(ClassCourseAssignment::class, 'teacher_id');
    }
    
    // <<< ĐÂY LÀ HÀM BỊ THIẾU GÂY RA LỖI
    /**
     * Lấy tất cả các lịch dạy của một giáo viên thông qua các lớp học phần của họ.
     */
    public function taughtSchedules()
    {
        return $this->hasManyThrough(
            Schedule::class,
            ClassCourseAssignment::class,
            'teacher_id', // Khóa ngoại trên bảng trung gian (class_course_assignments)
            'class_course_assignment_id', // Khóa ngoại trên bảng cuối cùng (schedules)
            'id', // Khóa chính trên bảng bắt đầu (users)
            'id'  // Khóa chính trên bảng trung gian (class_course_assignments)
        );
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'teacher_id');
    }

    public function makeupClasses()
    {
        return $this->hasMany(MakeupClass::class, 'teacher_id');
    }
}