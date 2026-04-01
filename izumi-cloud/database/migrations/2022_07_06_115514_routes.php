<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Routes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->bigInteger('department_id')->unsigned();
            $table->bigInteger('customer_id')->unsigned();
            $table->tinyInteger('route_fare_type');
            $table->bigInteger('fare');
            $table->bigInteger('highway_fee');
            $table->bigInteger('highway_fee_holiday');
            $table->tinyInteger('is_government_holiday')->nullable();
            $table->string('remark')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });

        Schema::create('route_store', function (Blueprint $table) {
            $table->bigInteger('route_id')->unsigned();
            $table->bigInteger('store_id')->unsigned();
            $table->timestamps();

            $table->foreign('route_id')->references('id')->on('routes')
                ->onDelete('restrict')
                ->onUpdate('restrict');

            $table->foreign('store_id')->references('id')->on('stores')
                ->onDelete('restrict')
                ->onUpdate('restrict');
        });

        Schema::create('route_non_delivery', function (Blueprint $table) {
            $table->bigInteger('route_id')->unsigned();
            $table->tinyInteger('number_at');
            $table->tinyInteger('is_week')->nullable();
            $table->timestamps();

            $table->foreign('route_id')->references('id')->on('routes')
                ->onDelete('restrict')
                ->onUpdate('restrict');
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
