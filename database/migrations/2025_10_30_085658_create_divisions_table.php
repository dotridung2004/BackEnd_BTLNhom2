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
        if (!Schema::hasTable('divisions')) {
            Schema::create('divisions', function (Blueprint $table) {
                $table->id(); // Cột ID tự tăng, khóa chính
                $table->string('code')->unique(); // Mã bộ môn, không trùng lặp
                $table->string('name'); // Tên bộ môn
                
                // Khóa ngoại liên kết đến bảng 'departments'
                // onDelete('cascade'): Nếu khoa bị xóa, các bộ môn thuộc khoa đó cũng bị xóa
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
        Schema::dropIfExists('divisions');
    }
};