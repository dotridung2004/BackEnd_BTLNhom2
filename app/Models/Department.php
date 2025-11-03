<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\Division;
use App\Models\Major;
use App\Models\Course;

class Department extends Model
{
    use HasFactory;

    /**
     * Các thuộc tính có thể gán hàng loạt (đã gộp)
     */
    protected $fillable = [
        'name',
        'code',
        'head_id',
        'description', // Giữ lại từ file 1
    ];

    /**
     * Lấy trưởng khoa (head of department).
     * (Giữ lại từ file 2 - có kiểu trả về)
     */
    public function head(): BelongsTo
    {
        return $this->belongsTo(User::class, 'head_id');
    }

    /**
     * Lấy danh sách các bộ môn thuộc khoa này.
     * (Giữ lại từ file 2 - có kiểu trả về và khóa ngoại)
     */
    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class, 'department_id');
    }

    /**
     * Lấy danh sách các ngành học thuộc khoa này.
     * (Giữ lại từ file 2 - có kiểu trả về và khóa ngoại)
     */
    public function majors(): HasMany
    {
        return $this->hasMany(Major::class, 'department_id');
    }

    /**
     * Lấy danh sách giảng viên thuộc khoa này QUA CÁC BỘ MÔN.
     * (Giữ lại từ file 1 - dùng hasManyThrough, logic này thường chính xác hơn)
     */
    public function teachers()
    {
        return $this->hasManyThrough(User::class, Division::class);
    }

    /**
     * Lấy danh sách các học phần do khoa này quản lý.
     * (Giữ lại từ file 2)
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'department_id');
    }
}
