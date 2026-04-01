<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInsuranceRateHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('insurance_rate_history', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('insurance_rates_id');
            $table->double('current_rate');
            $table->double('change_rate')->nullable();
            $table->date('applicable_date')->nullable();
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
        Schema::dropIfExists('insurance_rate_history');
    }
}
