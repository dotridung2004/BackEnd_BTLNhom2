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
            Schema::table('divisions', function (Blueprint $table) {
                // Thêm cột description kiểu TEXT, cho phép NULL
                // after('department_id') để thêm cột này sau cột department_id (tùy chọn)
                if (!Schema::hasColumn('divisions', 'description')) {
                    $table->text('description')->nullable()->after('department_id');
                }
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::table('divisions', function (Blueprint $table) {
                // Xóa cột description khi rollback
                if (Schema::hasColumn('divisions', 'description')) {
                    $table->dropColumn('description');
                }
            });
        }
    };