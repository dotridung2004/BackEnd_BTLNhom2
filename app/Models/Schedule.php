<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'class_course_assignment_id',
        'room_id',
        'date',
        'session',
        'topic',
        'status',
    ];
    public function classCourseAssignment(){
        return $this->belongsTo(Course::class);
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
