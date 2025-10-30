<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('class_student', function (Blueprint $table) {
            $table->id(); // Cột ID tự tăng cho bảng trung gian (tùy chọn nhưng nên có)

            // Khóa ngoại trỏ đến bảng 'classes'
            // Tên cột nên là 'class_model_id' để khớp với tên Model 'ClassModel'
            $table->foreignId('class_model_id')->constrained('classes')->onDelete('cascade');

            // Khóa ngoại trỏ đến bảng 'users' (cho sinh viên)
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');

            $table->timestamps(); // Thêm cột created_at và updated_at (tùy chọn)

            // Đảm bảo một sinh viên không bị thêm vào cùng một lớp nhiều lần
            $table->unique(['class_model_id', 'student_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_student');
    }
};