<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIsSendNotiToMovieSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movie_schedules', function (Blueprint $table) {
            $table->integer('is_send_noti')->after('time')->default(0);
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
            $table->dropColumn('is_send_noti');
        });
    }
}
