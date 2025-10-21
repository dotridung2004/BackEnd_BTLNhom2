<?php

namespace App\Models;

// 👇 THÊM DÒNG NÀY (để dùng Factory, theo chuẩn Laravel mới)
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    // 👇 THÊM DÒNG NÀY
    use HasFactory;

    protected $fillable = [
        'class_course_assignment_id',
        'room_id',
        'date',
        'session',
        'topic',
        'status',
    ];

    /**
     * 💡 SỬA LỖI:
     * Tự động chuyển đổi (cast) cột 'date' từ string thành đối tượng Carbon.
     * Điều này cho phép bạn gọi các hàm như ->toDateString()
     */
    protected $casts = [
        'date' => 'date', // 👈 THÊM DÒNG NÀY
    ];

    // --- (Các hàm quan hệ bên dưới đã đúng, giữ nguyên) ---

    public function classCourseAssignment(){
        return $this->belongsTo(ClassCourseAssignment::class, 'class_course_assignment_id');
    }

    public function room(){
        return $this->belongsTo(Room::class);
    }

    public function attendances(){
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests(){
        return $this->hasMany(LeaveRequest::class);
    }

    public function makeupClasses(){
        return $this->hasMany(MakeupClass::class,'original_schedule_id');
    }
}