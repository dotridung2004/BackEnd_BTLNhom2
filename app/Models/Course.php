<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Division; // Import Model Division

class Course extends Model
{
    protected $fillable = [
        'name',
        'code',
        'credits',
        'department_id',
        // XÓA 'division_id' vì bạn đã bỏ khỏi form, nhưng giữ lại 'subject_type'
        'subject_type',
        'description', // Thêm 'description' vào fillable để update
    ];

    public function department(){
        return $this->belongsTo(Department::class);
    }

    // XÓA quan hệ division vì đã bỏ khỏi form/logic CRUD
    /*
    public function division(){
        return $this->belongsTo(Division::class);
    }
    */
    
    public function classCourseAssignments(){
        return $this->hasMany(ClassCourseAssignment::class,'course_id');
    }
}
