<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Nên thêm HasFactory
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Import kiểu trả về
use Illuminate\Database\Eloquent\Relations\HasMany;   // Import kiểu trả về

class Department extends Model
{
    use HasFactory; // Nên thêm HasFactory

    protected $fillable = [
        'name',
        'head_id',
        'code', // Thêm 'code' nếu có trong migration và bạn muốn fillable
    ];

    /**
     * Lấy trưởng khoa (head of department).
     */
    public function head(): BelongsTo
    {
        // Quan hệ này đúng nếu bảng 'departments' có cột 'head_id' là khóa ngoại đến 'users.id'
        return $this->belongsTo(User::class, 'head_id');
    }

    /**
     * Lấy danh sách các bộ môn thuộc khoa này.
     */
    public function divisions(): HasMany
    {
        // Quan hệ này đúng nếu bảng 'divisions' có cột 'department_id'
        return $this->hasMany(Division::class, 'department_id'); // Nên chỉ rõ khóa ngoại
    }

    /**
     * Lấy danh sách các ngành học thuộc khoa này.
     */
    public function majors(): HasMany
    {
        // Quan hệ này đúng nếu bảng 'majors' có cột 'department_id'
        return $this->hasMany(Major::class, 'department_id'); // Nên chỉ rõ khóa ngoại
    }


    /**
     * Lấy danh sách giảng viên thuộc khoa này.
     * LƯU Ý: Quan hệ này chỉ hoạt động nếu bảng 'users' có cột 'department_id'.
     * Nếu giảng viên liên kết qua 'division' hoặc 'major', bạn cần dùng hasManyThrough
     * hoặc xóa quan hệ này nếu không cần thiết.
     * Việc dùng withCount('teachers') trong DepartmentController sẽ gây lỗi nếu không có cột này.
     */
    public function teachers(): HasMany
    {
        // Giả định bảng 'users' có cột 'department_id'
        return $this->hasMany(User::class, 'department_id');
    }


    /**
     * Lấy danh sách các học phần do khoa này quản lý (nếu có).
     */
    public function courses(): HasMany
    {
         // Quan hệ này đúng nếu bảng 'courses' có cột 'department_id'
        return $this->hasMany(Course::class, 'department_id'); // Nên chỉ rõ khóa ngoại
    }

    public function users()
    {
        return $this->hasMany(User::class, 'department_id');
    }

}