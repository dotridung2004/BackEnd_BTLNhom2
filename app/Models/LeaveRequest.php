<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $fillable = [
        'teacher_id',
        'schedule_id',
        'reason',
        'document_url',
        'status',
        'approved_by',
    ];
    public function teacher(){
        return $this->belongsTo(User::class,'teacher_id');
    }
    public function schedule(){
        return $this->belongsTo(Schedule::class);
    }
    public function approver(){
        return $this->belongsTo(User::class,'approved_by');
    }
}
