<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnAutoFlagToMovieSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movie_schedules', function (Blueprint $table) {
            $table->integer('auto_flag')->default(0)->after('is_send_noti');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movie_schedules', function (Blueprint $table) {
            $table->dropColumn('auto_flag');
        });
    }
}
