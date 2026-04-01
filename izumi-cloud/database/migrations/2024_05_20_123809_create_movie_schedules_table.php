<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovieSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movie_schedules', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('movie_id')->unsigned()->nullable();
            $table->date('date');
            $table->timestamps();

            $table->foreign('movie_id')->references('id')->on('movies')
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
        Schema::dropIfExists('movie_schedules');
    }
}
