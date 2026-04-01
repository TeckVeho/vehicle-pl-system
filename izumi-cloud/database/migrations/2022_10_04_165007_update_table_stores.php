<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTableStores extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('bussiness_classification')->nullable();
            $table->integer('delivery_destination_code')->nullable();
            $table->string('destination_name_kana')->nullable();
            $table->string('destination_name')->nullable();
            $table->string('post_code')->nullable();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('delivery_frequency')->nullable();
            $table->integer('quantity_delivery')->nullable();

            $table->string('first_sd_time')->nullable();
            $table->string('first_sd_sub_min_one')->nullable();
            $table->string('first_sd_sub_min_second')->nullable();

            $table->string('second_sd_time')->nullable();
            $table->string('second_sub_min_one')->nullable();
            $table->string('second_sub_min_second')->nullable();

            $table->integer('scheduled_time_first')->nullable();
            $table->boolean('vehicle_height_width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('width')->nullable();
            $table->boolean('parking_place')->nullable();
            $table->string('note_1')->nullable();
            $table->boolean('delivery_slip')->nullable();
            $table->string('note_2')->nullable();
            $table->integer('daisha')->nullable();
            $table->string('note_3')->nullable();
            $table->string('place')->nullable();
            $table->string('note_4')->nullable();
            $table->string('empty_recovery')->nullable();
            $table->boolean('key')->nullable();
            $table->string('note_5')->nullable();
            $table->boolean('security')->nullable();
            $table->string('cancel_method')->nullable();
            $table->string('grace_time')->nullable();
            $table->string('company_name')->nullable();
            $table->string('tel_number')->nullable();
            $table->string('tel_number_2')->nullable();
            $table->string('inside_rule')->nullable();
            $table->string('license')->nullable();
            $table->string('reception_or_entry')->nullable();
            $table->boolean('cerft_required')->nullable();
            $table->string('note_6')->nullable();
            $table->boolean('elevator')->nullable();
            $table->string('note_7')->nullable();
            $table->boolean('waiting_place')->nullable();
            $table->string('note_8')->nullable();
            $table->string('delivery_route_map_path')->nullable();
            $table->string('delivery_route_map_other_remark')->nullable();
            $table->string('parking_position_1_file_path')->nullable();
            $table->string('parking_position_1_other_remark')->nullable();
            $table->string('parking_position_2_file_path')->nullable();
            $table->string('parking_position_2_other_remark')->nullable();
            $table->string('pass_code')->nullable();
        });

        Schema::create(
            'delivery_manual',
            function (Blueprint $table) {
                $table->bigInteger('store_id')->unsigned();
                $table->string('content')->nullable();
                $table->timestamps();

                $table->foreign('store_id')->references('id')->on('stores')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');
            }
        );
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
