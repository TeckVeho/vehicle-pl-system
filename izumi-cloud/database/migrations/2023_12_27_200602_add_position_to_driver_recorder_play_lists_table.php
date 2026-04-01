<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPositionToDriverRecorderPlayListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_recorder_play_list', function (Blueprint $table) {
            $table->integer('position')->after('driver_recorder_id')->nullable();
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
        Schema::table('driver_recorder_play_list', function (Blueprint $table) {
            $table->dropColumn('position');
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });
    }
}
