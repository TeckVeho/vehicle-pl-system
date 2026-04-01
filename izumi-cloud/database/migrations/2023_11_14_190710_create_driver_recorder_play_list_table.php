<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverRecorderPlayListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('driver_recorder_play_list', function (Blueprint $table) {
            $table->bigInteger('driver_play_list_id')->unsigned();
            $table->bigInteger('driver_recorder_id')->unsigned();

            $table->foreign('driver_recorder_id')->references('id')->on('driver_recorders')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign('driver_play_list_id')->references('id')->on('driver_play_lists')
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
        Schema::dropIfExists('driver_recorder_play_list');
    }
}
