<?php
// Tên file: app/Models/Major.php
// *** ĐÃ CẬP NHẬT: Thêm filter 'role' cho quan hệ teachers() ***

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Major extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',          
        'name',          
        'department_id', 
        'description',   
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
     * (Sau khi đã chạy SQL cập nhật major_id cho giảng viên)
     */
    public function teachers(): HasMany
    {
        // 👇 **** SỬA ĐỔI **** 👇
        // Thêm bộ lọc để chỉ lấy Giảng viên/Trưởng khoa
        return $this->hasMany(User::class, 'major_id')
                    ->whereIn('role', ['teacher', 'head_of_department']);
        // 👆 **** KẾT THÚC SỬA ĐỔI **** 👆
    }
}