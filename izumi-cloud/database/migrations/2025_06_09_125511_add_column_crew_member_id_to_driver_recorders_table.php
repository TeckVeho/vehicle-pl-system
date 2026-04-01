<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnCrewMemberIdToDriverRecordersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_recorders', function (Blueprint $table) {
            $table->integer('crew_member_id')->after('flag_send_noti')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_recorders', function (Blueprint $table) {
            $table->dropColumn('crew_member_id');
        });
    }
}
