<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFiledRecruitmentToDepartmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('province_name')->nullable();
            $table->string('province_md5')->nullable();
            $table->text('interview_address')->nullable();
            $table->string('interview_address_url')->nullable();
            $table->text('path_for_interview_address')->nullable();
            $table->integer('interview_pic')->nullable();
            $table->string('interview_pic_line_work')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('department', function (Blueprint $table) {
            //
        });
    }
}
