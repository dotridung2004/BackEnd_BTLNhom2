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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_course_assignment_id')->constrained('class_course_assignments')->onDelete('cascade');
            $table->foreignId('room_id')->nullable()->constrained('rooms')->onDelete('set null');
            $table->date('date');
            $table->string('session'); // ca/tiết
            $table->string('topic')->nullable();
            $table->enum('status', ['scheduled','taught','cancelled','makeup'])->default('scheduled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
