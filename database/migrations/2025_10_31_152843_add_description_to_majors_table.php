<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// [Bên trong file ..._add_mo_ta_to_majors_table.php]
    
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('majors', function (Blueprint $table) {
            // Đổi 'mo_ta' thành 'description'
            $table->text('description')->nullable()->after('name'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('majors', function (Blueprint $table) {
            // Đổi 'mo_ta' thành 'description'
            $table->dropColumn('description');
        });
    }
};
