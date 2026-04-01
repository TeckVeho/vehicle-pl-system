<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeTimesheetDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_timesheet_data', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->index();
            $table->bigInteger('department_id')->index();
            $table->tinyInteger('job_type')->index();
            $table->double('scheduled_wh');
            $table->double('overtime_salary_wh');
            $table->double('midnight_wh');
            $table->double('holiday_wh');
            $table->double('actual_wh');
            $table->double('working_day');
            $table->integer('year')->index();
            $table->integer('month')->index();
            $table->double('transportation_cp');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_timesheet_data');
    }
}
