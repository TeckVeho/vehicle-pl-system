<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovieUserLikeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movie_user_like', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('movie_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->integer('like_or_dislike')->default(0);
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
        Schema::dropIfExists('movie_user_like');
    }
}
