<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Department; // 👈 Import Department nếu có quan hệ

class Discipline extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong cơ sở dữ liệu.
     * Laravel thường tự suy ra tên bảng là 'disciplines' (số nhiều của 'Discipline').
     * Chỉ định rõ ràng nếu tên bảng của bạn khác.
     * Ví dụ: protected $table = 'bo_mon';
     */
    // protected $table = 'disciplines';

    /**
     * Các thuộc tính có thể được gán hàng loạt (mass assignable).
     * Thêm các cột trong bảng 'disciplines' của bạn vào đây.
     */
    protected $fillable = [
        'name',
        'department_id', // Giả sử Bộ môn thuộc về một Khoa
        // Thêm các cột khác nếu có...
    ];

    /**
     * Định nghĩa quan hệ: Một Bộ môn thuộc về một Khoa.
     * Bỏ comment và sửa khóa ngoại ('department_id') nếu cần.
     */
    /*
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    */

    /**
     * Định nghĩa quan hệ: Một Bộ môn có nhiều Học phần. (Tùy chọn)
     * Bỏ comment nếu bạn muốn truy cập các học phần từ bộ môn.
     */
    /*
    public function courses()
    {
        // Giả sử khóa ngoại trong bảng 'courses' là 'discipline_id'
        return $this->hasMany(Course::class, 'discipline_id');
    }
    */
}
