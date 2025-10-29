<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Major extends Model
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
     * Lấy khoa mà ngành học này thuộc về.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Lấy danh sách giảng viên thuộc ngành học này.
     * (Giả định Giảng viên là 'User')
     */
    public function teachers(): HasMany
    {
        // Nếu bạn có model 'Teacher' riêng, hãy đổi User::class thành Teacher::class
        return $this->hasMany(User::class);
    }
}
