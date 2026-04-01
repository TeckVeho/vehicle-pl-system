<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleItpDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_itp_data', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('km_l,etc')->index();
            $table->bigInteger('vehicle_id')->index();
            $table->bigInteger('department_id')->index();
            $table->integer('year')->index();
            $table->integer('month')->index();
            $table->double('cost');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle_itp_data');
    }
}
