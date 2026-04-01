<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoviesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('file_id')->unsigned()->nullable();
            $table->bigInteger('thumbnail_file_id')->unsigned()->nullable();
            $table->string('title')->nullable();
            $table->string('file_length')->nullable();
            $table->string('content')->nullable();
            $table->json('tag')->nullable();
            $table->integer('position');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('file_id')->references('id')->on('files')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('thumbnail_file_id')->references('id')->on('files')
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
        Schema::dropIfExists('movies');
    }
}
