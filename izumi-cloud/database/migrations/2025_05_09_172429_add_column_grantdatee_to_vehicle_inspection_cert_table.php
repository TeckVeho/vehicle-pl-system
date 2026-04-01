<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnGrantdateeToVehicleInspectionCertTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicle_inspection_cert', function (Blueprint $table) {
            $table->string('GrantdateE')->nullable()->after('EntryNoCarNo');
            $table->string('GrantdateY')->nullable()->after('GrantdateE');
            $table->string('GrantdateM')->nullable()->after('GrantdateY');
            $table->string('GrantdateD')->nullable()->after('GrantdateM');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicle_inspection_cert', function (Blueprint $table) {
            $table->dropColumn('GrantdateE');
            $table->dropColumn('GrantdateY');
            $table->dropColumn('GrantdateM');
            $table->dropColumn('GrantdateD');
        });
    }
}
