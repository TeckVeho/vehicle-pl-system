<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFlagSendNotiToDriverRecordersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_recorders', function (Blueprint $table) {
            $table->integer('flag_send_noti')->default(0);
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
            $table->dropColumn('flag_send_noti');
        });
    }
}
