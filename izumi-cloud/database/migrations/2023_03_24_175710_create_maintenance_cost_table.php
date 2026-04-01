<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintenanceCostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintenance_costs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('vehicle_id');
            $table->string('vehicle_identification_number');
            $table->bigInteger('cost_code');
            $table->string('plate');
            $table->date('scheduled_date')->nullable();
            $table->date('maintained_date')->nullable();
            $table->double('total_amount_excluding_tax')->nullable();
            $table->double('discount')->nullable();
            $table->double('total_amount_including_tax')->nullable();
            $table->text('note')->nullable();
            $table->tinyInteger('status')->nullable();
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
        Schema::dropIfExists('maintenance_cost');
    }
}
