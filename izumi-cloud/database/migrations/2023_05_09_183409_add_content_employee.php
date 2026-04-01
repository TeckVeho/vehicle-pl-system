<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContentEmployee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('company_car', 20)->after('schedule_working_hours')->nullable();
            $table->string('etc_card', 20)->after('company_car')->nullable();
            $table->string('fuel_card', 20)->after('etc_card')->nullable();
            $table->string('other', 200)->after('fuel_card')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
