<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VehicleNoPlate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles_datas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('vehicle_id');

            $table->string('tire')->nullable();
            $table->date('tire_replacement_date')->nullable();

            $table->string('starter_motor')->nullable();
            $table->date('starter_motor_date')->nullable();

            $table->string('alternator')->nullable();
            $table->date('alternator_date')->nullable();

            $table->string('glass')->nullable();
            $table->date('glass_date')->nullable();
            $table->string('body_id')->nullable();
            $table->date('body_id_date')->nullable();
            $table->string('camera_monitor')->nullable();
            $table->date('camera_monitor_date')->nullable();
            $table->string('gate')->nullable();
            $table->date('gate_date')->nullable();
            $table->string('other')->nullable();
            $table->date('other_date')->nullable();
            $table->string("remark_01")->nullable();
            $table->string("remark_02")->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_no_number_plate_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('vehicle_id');
            $table->dateTime('date');
            $table->string('no_number_plate');
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('date');
            $table->index('no_number_plate');
        });

        Schema::create('vehicle_mileage_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('vehicle_id');
            $table->dateTime('date');
            $table->double('mileage');
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('date');
            $table->index('mileage');
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
