<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewCloumnToDriverRecordersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_recorders', function (Blueprint $table) {
            $table->integer('type_one')->after('remark');
            $table->integer('type_two')->after('type_one');
            $table->integer('shipper')->after('type_two');
            $table->integer('accident_classification')->after('shipper');
            $table->integer('place_of_occurrence')->after('accident_classification');
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
            $table->dropColumn('type_one');
            $table->dropColumn('type_two');
            $table->dropColumn('shipper');
            $table->dropColumn('accident_classification');
            $table->dropColumn('place_of_occurrence');
        });
    }
}
