<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    // (Các thuộc tính $fillable của bạn ở đây...)
    protected $fillable = [
        'name',
        'code',
        'head_id',
        'description',
    ];

    // (Hàm 'divisions' và 'teachers' của bạn ở đây...)
    public function divisions()
    {
        return $this->hasMany(Division::class);
    }

    public function teachers()
    {
        return $this->hasManyThrough(User::class, Division::class);
    }
    
    // 👇 **** BẮT ĐẦU THÊM MỚI **** 👇

    /**
     * Lấy thông tin người dùng (User) là trưởng khoa.
     */
    public function head()
    {
        // 'head_id' là khóa ngoại, 'id' là khóa chính trên bảng 'users'
        return $this->belongsTo(User::class, 'head_id', 'id');
    }

    /**
     * Lấy các ngành học (Majors) thuộc khoa này.
     * (Giả định: Khoa có nhiều Ngành)
     */
    public function majors()
    {
        return $this->hasMany(Major::class);
    }
    
    // 👆 **** KẾT THÚC THÊM MỚI **** 👆
}