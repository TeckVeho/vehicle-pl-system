<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('department_id');
            $table->string('driving_classification')->nullable();
            $table->string('tonnage')->nullable();
            $table->string('truck_classification')->nullable();
            $table->integer('truck_classification_number')->nullable();
            $table->string('truck_classification_2')->nullable();
            $table->string('manufactor')->nullable();
            $table->string('first_registration')->nullable();
            $table->string('box_distinction')->nullable();
            $table->string('inspection_expiration_date')->nullable();
            $table->string('scrap_date')->nullable();
            $table->string('vehicle_identification_number');
            $table->string('owner')->nullable();
            $table->string('etc_certification_number')->nullable();
            $table->string('etc_number')->nullable();
            $table->string('fuel_card_number_1')->nullable();
            $table->string('fuel_card_number_2')->nullable();
            $table->string('driving_recorder')->nullable();
            $table->string('box_shape')->nullable();
            $table->string('mount')->nullable();
            $table->string('refrigerator')->nullable();
            $table->string('eva_type')->nullable();
            $table->tinyInteger('gate')->nullable();
            $table->boolean('humidifier')->default(false);
            $table->string('type')->nullable();
            $table->string('motor')->nullable();
            $table->double('displacement')->nullable();
            $table->integer('length')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->double('maximum_loading_capacity')->nullable();
            $table->double('vehicle_total_weight')->nullable();
            $table->integer('in_box_length')->nullable();
            $table->integer('in_box_width')->nullable();
            $table->integer('in_box_height')->nullable();
            $table->integer('voluntary_insurance')->nullable();
            $table->string('liability_insurance_period')->nullable();
            $table->string('insurance_company')->nullable();
            $table->string('agent')->nullable();
            $table->string('tire_size')->nullable();
            $table->string('battery_size')->nullable();
            $table->string('optional_detail')->nullable();
            $table->double('monthly_mileage')->nullable();
            $table->string('remark_old_car_1')->nullable();
            $table->string('remark_old_car_2')->nullable();
            $table->string('remark_old_car_3')->nullable();
            $table->string('remark_old_car_4')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('department_id');
            $table->index('tonnage');
            $table->index('truck_classification_number');
            $table->index('inspection_expiration_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
}
