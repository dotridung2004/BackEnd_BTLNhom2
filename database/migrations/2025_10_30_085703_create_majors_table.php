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
        // Kiểm tra xem bảng đã tồn tại chưa trước khi tạo
        if (!Schema::hasTable('majors')) {
            Schema::create('majors', function (Blueprint $table) {
                $table->id(); // Cột ID tự tăng, khóa chính
                $table->string('code')->unique(); // Mã ngành học, không trùng lặp
                $table->string('name'); // Tên ngành học

                // Khóa ngoại liên kết đến bảng 'departments'
                $table->foreignId('department_id')
                      ->constrained('departments') // Ràng buộc khóa ngoại với bảng 'departments'
                      ->onDelete('cascade');

                $table->timestamps(); // Tự động tạo cột created_at và updated_at
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa bảng nếu tồn tại khi rollback
        Schema::dropIfExists('majors');
    }
};