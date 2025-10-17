<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    protected $fillable = [
        'name',
        'semester',
        'academic_year',
        'department_id',
    ];
    public function department(){
        return $this->belongsTo(Department::class);
    }
    public function classCourseAssignment(){
        return $this->hasMany(ClassCourseAssignment::class,'class_id');
    }
}
