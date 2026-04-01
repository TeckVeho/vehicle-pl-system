<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Courses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('course_code')->unique();
            $table->date('start_date')->nullable();;
            $table->date('end_date')->nullable();
            $table->tinyInteger('course_type');
            $table->tinyInteger('bin_type');
            $table->tinyInteger('delivery_type');
            $table->time('start_time');
            $table->integer('gate');
            $table->integer('wing');
            $table->integer('tonnage');
            $table->tinyInteger('quantity');
            $table->bigInteger('allowance');
            $table->bigInteger('department_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('department_id')->references('id')->on('departments')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });

        Schema::create('course_route', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('route_id')->unsigned();
            $table->bigInteger('course_id')->unsigned();
            $table->integer('position');
            $table->foreign('route_id')->references('id')->on('routes')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->foreign('course_id')->references('id')->on('courses')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });

        Schema::create('course_schedule', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('route_id')->unsigned();
            $table->bigInteger('course_id')->unsigned();
            $table->date('date');

            $table->foreign('route_id')->references('id')->on('routes')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->foreign('course_id')->references('id')->on('courses')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });

        Schema::create('course_non_delivery', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('route_id')->unsigned();
            $table->date('date');

            $table->foreign('route_id')->references('id')->on('routes')
                ->onDelete('restrict')
                ->onUpdate('restrict');
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
