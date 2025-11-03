<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * (Hàm này sẽ chạy khi bạn gõ 'php artisan migrate')
     */
    public function up(): void
    {
        Schema::table('class_course_assignments', function (Blueprint $table) {
            
            // Thêm cột division_id (kiểu BIGINT, không dấu, cho phép NULL)
            // Đặt nó sau cột 'teacher_id' cho dễ nhìn
            $table->unsignedBigInteger('division_id')->nullable()->after('teacher_id');

            // (Tùy chọn) Thêm khóa ngoại để liên kết an toàn
            // $table->foreign('division_id')
            //       ->references('id')
            //       ->on('divisions')
            //       ->onDelete('set null'); // Nếu xóa bộ môn, trường này sẽ tự set về NULL
        });
    }

    /**
     * Reverse the migrations.
     * (Hàm này sẽ chạy nếu bạn cần hoàn tác)
     */
    public function down(): void
    {
        Schema::table('class_course_assignments', function (Blueprint $table) {
            
            // (Tùy chọn) Xóa khóa ngoại trước nếu bạn đã thêm
            // $table->dropForeign(['division_id']);
            
            // Xóa cột division_id
            $table->dropColumn('division_id');
        });
    }
};