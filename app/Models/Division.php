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
     * Các trường được phép gán hàng loạt.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'department_id',
    ];

    /**
     * Lấy khoa mà bộ môn này thuộc về.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Lấy danh sách giảng viên thuộc bộ môn này.
     * (Giả định Giảng viên là 'User')
     */
    public function teachers(): HasMany
{
     return $this->hasMany(User::class, 'division_id'); // Khóa ngoại là 'division_id'
}

    /**
     * Lấy danh sách các học phần thuộc bộ môn này.
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
