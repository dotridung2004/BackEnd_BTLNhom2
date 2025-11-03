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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên phòng (ví dụ: 'K1-201')
            $table->string('building', 50)->nullable(); // Tòa nhà (ví dụ: 'K1')
            $table->integer('floor')->nullable(); // Tầng (ví dụ: 2)
            $table->integer('capacity')->nullable(); // Sức chứa
            $table->string('room_type', 100)->nullable(); // Loại phòng (ví dụ: 'Lí thuyết')
            $table->string('status', 50)->default('Hoạt động'); // Trạng thái
            $table->timestamps();
            
            // Cột 'location' đã bị xóa, nên bỏ nó ra khỏi migration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};