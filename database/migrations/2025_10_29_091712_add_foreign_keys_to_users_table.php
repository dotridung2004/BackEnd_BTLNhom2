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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'division_id')) {
                $table->foreignId('division_id')
                      ->nullable()
                      ->constrained('divisions')
                      ->onDelete('set null');
            }
            if (!Schema::hasColumn('users', 'major_id')) {
                 $table->foreignId('major_id')
                      ->nullable()
                      ->constrained('majors')
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 👇 SỬA LẠI HÀM DOWN NHƯ SAU 👇
            // Chỉ cần xóa cột, khóa ngoại thường sẽ tự động bị xóa theo
             if (Schema::hasColumn('users', 'division_id')) {
                 // Thử xóa khóa ngoại trước (optional, nhưng an toàn hơn nếu tên khóa ngoại đúng)
                 // $table->dropForeign(['division_id']); // Bạn có thể bỏ qua dòng này
                 $table->dropColumn('division_id'); 
             }
             if (Schema::hasColumn('users', 'major_id')) {
                 // $table->dropForeign(['major_id']); // Bạn có thể bỏ qua dòng này
                 $table->dropColumn('major_id');
             }
            // 👆 KẾT THÚC SỬA ĐỔI 👆
        });
    }
};

