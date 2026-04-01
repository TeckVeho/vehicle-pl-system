<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MaintenanceLease extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintenance_leases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('vehicle_id')->nullable();
            $table->string('no_number_plate');
            $table->bigInteger('department_id')->nullable();
            $table->date('start_of_leasing')->nullable();
            $table->date('end_of_leasing')->nullable();
            $table->string('leasing_period')->nullable();
            $table->string('leasing_company')->nullable();
            $table->string('garage')->nullable();
            $table->string('tel')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('vehicle_id');
            $table->index('garage');
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
