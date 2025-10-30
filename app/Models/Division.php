<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    use HasFactory;

    /**
     * Các trường được phép gán hàng loạt (mass assignable).
     * Laravel sẽ chỉ cho phép lưu dữ liệu vào các cột được liệt kê ở đây
     * khi bạn dùng phương thức create() hoặc update() với một mảng dữ liệu.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'department_id',
        'description', // <--- Đã thêm, chính xác
    ];

    /**
     * Lấy khoa mà bộ môn này thuộc về.
     */
    public function department(): BelongsTo
    {
        // Đảm bảo khóa ngoại đúng là 'department_id' trên bảng 'divisions'
        return $this->belongsTo(Department::class, 'department_id');
    }

    /**
     * Lấy danh sách giảng viên (Users) thuộc bộ môn này.
     */
    public function teachers(): HasMany
    {
        // Đảm bảo bảng 'users' có cột khóa ngoại 'division_id'
        return $this->hasMany(User::class, 'division_id');
    }

    /**
     * Lấy danh sách các học phần (Courses) thuộc bộ môn này.
     */
    public function courses(): HasMany
    {
        // Đảm bảo bảng 'courses' có cột khóa ngoại 'division_id'
        return $this->hasMany(Course::class, 'division_id');
    }
}

