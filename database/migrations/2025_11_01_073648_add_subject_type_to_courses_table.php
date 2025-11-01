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
        Schema::table('courses', function (Blueprint $table) {
            // Thêm cột 'subject_type' với 2 giá trị 'Bắt buộc', 'Tùy chọn'
            // Cột này có thể để NULL (nullable) và nằm sau cột 'division_id'
            $table->enum('subject_type', ['Bắt buộc', 'Tùy chọn'])
                  ->nullable()
                  ->after('division_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Hàm down sẽ xóa cột này đi nếu bạn muốn rollback
            $table->dropColumn('subject_type');
        });
    }
};