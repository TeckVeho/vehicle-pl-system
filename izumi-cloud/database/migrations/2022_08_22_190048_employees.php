<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Employees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('employees');
        Schema::create('employees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('employee_code');
            $table->string('name');
            $table->tinyInteger('sex');
            $table->date('birthday');
            $table->date('hire_start_date');
            $table->date('retirement_date')->nullable();

            $table->tinyInteger('license_type');
            $table->tinyInteger('employee_type');
            $table->tinyInteger('job_type');

            $table->integer('grade')->nullable();
            $table->integer('employee_grade_2')->nullable();
            $table->integer('boarding_employee_grade')->nullable();
            $table->integer('boarding_employee_grade_2')->nullable();
            $table->integer('transportation_compensation')->default(0);
            $table->integer('daily_transportation_cp')->default(0);
            $table->double('midnight_worktime')->default(0);
            $table->double('schedule_working_hours')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employee_department', function (Blueprint $table) {
            $table->bigInteger('employee_id')->unsigned();
            $table->bigInteger('department_id')->unsigned();
            $table->date('start_date')->nullable();
            $table->json('employee_data');
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('department_id')->references('id')->on('departments')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });

        Schema::create('employee_working_department', function (Blueprint $table) {
            $table->bigInteger('employee_id')->unsigned();
            $table->bigInteger('department_id')->unsigned();
            $table->date('start_date');
            $table->date('end_date')->nullable();

            $table->integer('grade')->nullable();
            $table->integer('employee_grade_2')->nullable();
            $table->integer('boarding_employee_grade')->nullable();
            $table->integer('boarding_employee_grade_2')->nullable();
            $table->integer('transportation_compensation')->default(0);
            $table->integer('daily_transportation_cp')->default(0);
            $table->double('midnight_worktime')->default(0);
            $table->double('schedule_working_hours')->default(0);
            $table->boolean('is_support')->default(false);
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('department_id')->references('id')->on('departments')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });

        Schema::create('employee_course', function (Blueprint $table) {
            $table->bigInteger('employee_id')->unsigned();
            $table->bigInteger('course_id')->unsigned();
            $table->bigInteger('department_id')->unsigned();
            $table->boolean('is_support')->default(false);
            $table->integer('position');

            $table->foreign('employee_id')->references('id')->on('employees')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('course_id')->references('id')->on('courses')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
