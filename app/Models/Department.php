<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
        'head_id',
    ];
    public function users(){
        return $this->belongsToMany(User::class);
    }
    public function courses(){
        return $this->hasMany(Course::class);    
    }
    public function head(){
        return $this->belongsTo(User::class,'head_id');
    }
}
