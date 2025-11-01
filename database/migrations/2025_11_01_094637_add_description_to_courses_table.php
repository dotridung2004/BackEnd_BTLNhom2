<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Thêm cột 'description' vào bảng 'courses'.
     */
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Thêm cột 'description' kiểu TEXT (phù hợp với Mô tả dài)
            // Đặt nó sau cột 'subject_type' nếu bạn có cột đó, hoặc sau cột cuối cùng hiện tại.
            $table->text('description')->nullable()->after('subject_type');
        });
    }

    /**
     * Reverse the migrations.
     * Hoàn tác: Xóa cột 'description' khỏi bảng 'courses'.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};