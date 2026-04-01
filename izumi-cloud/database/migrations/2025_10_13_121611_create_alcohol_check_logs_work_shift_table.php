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
        Schema::create('alcohol_check_logs_work_shift', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->nullable();//not use for Timesheet
            $table->string('employee_name');//not use for Timesheet
            $table->string('employee_code');
            $table->string('no_number_plate')->nullable();
            $table->tinyInteger('type');//0=>when comming work / 1=>when leaving work
            $table->timestamp('date');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alcohol_check_logs_work_shift');
    }
};
