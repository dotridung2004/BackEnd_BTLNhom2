<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <-- 1. ADD THIS IMPORT

class User extends Authenticatable
{
    // 👇 2. ADD HasApiTokens HERE 👇
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable; // <-- ADDED HasApiTokens

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone_number',
        'avatar_url',
        'gender',
        'date_of_birth',
        'role',
        'status',
    ];

    // --- Relationships ---
    public function department(){
        return $this->belongsTo(Department::class);
    }
    public function classCourseAssignments(){
        return $this->hasMany(ClassCourseAssignment::class,'teacher_id');
    }
    public function attendances(){
        return $this->hasMany(Attendance::class,'student_id');
    }
    public function leaveRequests(){
        return $this->hasMany(LeaveRequest::class,'teacher_id');
    }
    public function makeupClasses(){
        return $this->hasMany(MakeupClass::class,'teacher_id');
    }
    // --- End Relationships ---

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // 'password' => 'hashed', // Automatically handled in Laravel 10+ if not specified
    ];
}