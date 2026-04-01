<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnFromToInMovieSchedules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movie_schedules', function (Blueprint $table) {
            $table->date('from')->after('time')->nullable();
            $table->date('to')->after('from')->nullable();
            $table->integer('assign_type')->after('to')->default(1);
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
            $table->dropColumn('from');
            $table->dropColumn('to');
            $table->dropColumn('assign_type');
        });
    }
}
