<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Sửa đổi bảng 'courses' đã tồn tại
        Schema::table('courses', function (Blueprint $table) {
            // Thêm cột division_id nếu chưa tồn tại
            if (!Schema::hasColumn('courses', 'division_id')) {
                 $table->foreignId('division_id')
                      ->nullable() // Cho phép NULL
                      ->constrained('divisions') // Ràng buộc khóa ngoại với bảng 'divisions'
                      ->onDelete('set null'); // Đặt thành NULL nếu division bị xóa
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa cột và khóa ngoại khi rollback
        Schema::table('courses', function (Blueprint $table) {
             // Kiểm tra cột tồn tại trước khi xóa
             if (Schema::hasColumn('courses', 'division_id')) {
                 $table->dropForeign(['division_id']); // Xóa ràng buộc khóa ngoại trước
                 $table->dropColumn('division_id');    // Sau đó mới xóa cột
             }
        });
    }
};
