<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreIndexTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('id');
            $table->index('name');
            $table->index('role');
            $table->index('department_code');
            $table->index('deleted_at');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->index('employee_code');
            $table->index('name');
            $table->index('retirement_date');
            $table->index('deleted_at');
        });

        Schema::table('employee_working_department', function (Blueprint $table) {
            $table->index('start_date');
            $table->index('end_date');
            $table->index('is_support');
        });

        Schema::table('employee_department', function (Blueprint $table) {
            $table->index('start_date');
        });

        Schema::table('routes', function (Blueprint $table) {
            $table->index('name');
            $table->index('deleted_at');
        });
        Schema::table('route_non_delivery', function (Blueprint $table) {
            $table->index('number_at');
            $table->index('is_week');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->index('store_name');
            $table->index('deleted_at');
        });

        Schema::table('government_holiday', function (Blueprint $table) {
            $table->index('date');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->index('course_code');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('deleted_at');
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
