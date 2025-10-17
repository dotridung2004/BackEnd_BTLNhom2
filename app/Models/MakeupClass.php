<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MakeupClass extends Model
{
    protected $fillable = [
        'teacher_id',
        'original_schedule_id',
        'new_schedule_id',
        'status',
    ];
    public function teacher(){
        return $this->belongsTo(User::class,'teacher_id');
    }
    public function originalSchedule(){
        return $this->belongsTo(Schedule::class,'original_schedule_id');
    }
    public function newSchedule(){
        return $this->belongsTo(Schedule::class,'new_schedule_id');
    }
}
