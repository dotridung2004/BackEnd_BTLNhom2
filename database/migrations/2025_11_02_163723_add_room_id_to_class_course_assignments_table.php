<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoomIdToClassCourseAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('class_course_assignments', function (Blueprint $table) {
            // ✅ THÊM DÒNG NÀY
            // Thêm cột 'room_id' (kiểu số, có thể null, nằm sau cột 'division_id')
            $table->unsignedBigInteger('room_id')->nullable()->after('division_id');
            
            // (Tùy chọn) Thêm khóa ngoại nếu bạn muốn
            // $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('class_course_assignments', function (Blueprint $table) {
            // (Bỏ comment dòng này nếu bạn thêm khóa ngoại ở trên)
            // $table->dropForeign(['room_id']);
            
            // ✅ THÊM DÒNG NÀY
            $table->dropColumn('room_id');
        });
    }
}