<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFlagSendNotiToUserContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_contacts', function (Blueprint $table) {
            $table->integer('flag_send_noti')->default(0)->after('personal_tel');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_contacts', function (Blueprint $table) {
            $table->dropColumn('flag_send_noti');
        });
    }
}
