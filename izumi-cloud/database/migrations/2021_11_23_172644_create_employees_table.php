<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->string('employee_code')->nullable();
            $table->string('employee_name')->nullable();
            $table->string('employment_type_code')->nullable();
            $table->string('sex')->nullable();
            $table->string('birthday')->nullable();
            $table->string('hire_start_date')->nullable();
            $table->string('retirement_date')->nullable();
            $table->string('department_code')->nullable();
            $table->string('job_type_code')->nullable();
            $table->string('change_department_date')->nullable();
            $table->string('change_department_code')->nullable();
            $table->string('department_code_new')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
