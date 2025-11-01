<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    /**
     * Các thuộc tính có thể gán hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'building',
        'floor',
        'capacity',
        'room_type',
        'status',
        'description',
    ];
    
    /**
     * Lấy các lịch học liên quan đến phòng học này.
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}