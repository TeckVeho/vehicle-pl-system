<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIsLikeMovieInAppOrListsToMovieWatchingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('movie_watching', function (Blueprint $table) {
            $table->time('time')->after('date')->nullable();
            $table->integer('is_like_app')->after('time')->default(0);
            $table->integer('is_like_list')->after('is_like_app')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('movie_watching', function (Blueprint $table) {
            $table->dropColumn('time');
            $table->dropColumn('is_like_app');
            $table->dropColumn('is_like_list');
        });
    }
}
