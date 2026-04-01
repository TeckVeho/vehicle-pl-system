<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleDataOrcAiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_data_orc_ai', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('vehicle_id');
            $table->string('certificate_number')->nullable();
            $table->string('issue_date')->nullable();
            $table->string('vehicle_identification_number')->nullable();
            $table->string('insurance_period_1')->nullable();
            $table->string('insurance_period_2')->nullable();
            $table->string('address')->nullable();
            $table->string('policyholder')->nullable();
            $table->string('change_item')->nullable();
            $table->string('jurisdiction_store_name_and_location')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('location')->nullable();
            $table->string('insurance_fee')->nullable();
            $table->string('financial_institution_name')->nullable();
            $table->string('seal')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
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
        Schema::dropIfExists('vehicle_data_orc_ai');
    }
}
