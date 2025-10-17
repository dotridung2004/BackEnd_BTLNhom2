<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
