<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * (Chỉ chứa các cột có thật trong migration)
     */
    protected $fillable = [
        'class_course_assignment_id',
        'room_id',
        'date',
        'session',
        'topic',
        'status',
    ];

    /**
     * Tự động cast cột 'date' thành đối tượng Carbon.
     */
    protected $casts = [
        'date' => 'date',
    ];

    // --- Các hàm quan hệ (Giữ nguyên) ---

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