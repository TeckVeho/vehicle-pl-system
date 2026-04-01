<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quotation_route_segments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('route_id')->comment('FK đến quotation_routes');
            $table->unsignedBigInteger('from_location_id')->comment('FK đến quotation_route_locations');
            $table->unsignedBigInteger('to_location_id')->comment('FK đến quotation_route_locations');
            
            $table->tinyInteger('segment_order')->unsigned()->comment('Thứ tự đoạn');
            
            $table->decimal('distance_km', 10, 2)->comment('距離');
            $table->integer('driving_time_minutes')->comment('運転時間');
            
            $table->decimal('highway_fee', 10, 2)->default(0)->comment('高速料金');
            $table->decimal('fuel_cost', 10, 2)->default(0)->comment('燃料費');
            
            $table->enum('road_type', ['highway', 'national', 'prefectural', 'local'])->nullable();
            $table->string('highway_name', 200)->nullable()->comment('高速道路名');
            $table->text('route_description')->nullable()->comment('ルート概要');
            
            $table->string('traffic_condition', 50)->nullable()->comment('交通状況');
            $table->string('weather_condition', 50)->nullable()->comment('天候');
            $table->text('notes')->nullable()->comment('備考');
            
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('route_id')->references('id')->on('quotation_routes')->onDelete('cascade');
            $table->foreign('from_location_id')->references('id')->on('quotation_route_locations')->onDelete('cascade');
            $table->foreign('to_location_id')->references('id')->on('quotation_route_locations')->onDelete('cascade');
            
            $table->index(['route_id', 'segment_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_route_segments');
    }
};
