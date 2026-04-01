<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeRequirePersonalTelToUserContactsTabe extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_contacts', function (Blueprint $table) {
            $table->string('personal_tel')->nullable()->change();
            $table->integer('flag_check_personal_contact_info')->default(0);
            $table->integer('flag_check_emergency_contact_info_1')->default(0);
            $table->integer('flag_check_emergency_contact_info_2')->default(0);
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
            $table->string('personal_tel')->nullable(false)->change();
            $table->dropColumn('flag_check_personal_contact_info');
            $table->dropColumn('flag_check_emergency_contact_info_1');
            $table->dropColumn('flag_check_emergency_contact_info_2');
        });
    }
}
