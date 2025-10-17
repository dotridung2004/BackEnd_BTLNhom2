<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'name',
        'code',
        'credits',
        'department_id',
    ];
    public function department(){
        return $this->belongsTo(Department::class);
    }
    public function classCourseAssignments(){
        return $this->hasMany(ClassCourseAssignment::class,'course_id');
    }
}
