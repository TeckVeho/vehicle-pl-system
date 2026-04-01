<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VehicleDepartmentHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_department_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('vehicle_id');
            $table->dateTime('date');
            $table->bigInteger('department_id')->unsigned();
            $table->timestamps();

            $table->index('vehicle_id');
            $table->index('date');
            $table->index('department_id');
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
