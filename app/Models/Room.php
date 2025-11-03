<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // <-- THÊM
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory; // <-- THÊM

    /**
     * ✅ SỬA LỖI:
     * Cập nhật $fillable để khớp với file database_seed.sql
     * Bỏ 'location' và thêm các trường mới.
     */
    protected $fillable = [
        'name',
        'building',
        'floor',
        'capacity',
        'room_type',
        'status',
        'description', // <-- Thêm trường này từ file seed
    ];

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}