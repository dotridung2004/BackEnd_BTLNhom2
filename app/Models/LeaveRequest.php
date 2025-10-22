<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MakeupClass;
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
    public function makeupClass()
    {
        // hasOne(ModelLienQuan, 'khoa_ngoai_tren_bang_lien_quan', 'khoa_cuc_bo_tren_bang_nay')
        return $this->hasOne(MakeupClass::class, 'original_schedule_id', 'schedule_id');
    }
}
