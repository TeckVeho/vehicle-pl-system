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
        Schema::create('quotation_route_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('route_id')->comment('FK đến quotation_routes');
            
            $table->tinyInteger('sequence_order')->unsigned()->comment('Thứ tự: 1, 2, 3...');
            $table->enum('location_type', ['pickup', 'delivery', 'return', 'waypoint']);
            
            $table->string('location_name', 200)->nullable()->comment('Tên địa điểm');
            $table->string('address', 500)->comment('Địa chỉ đầy đủ');
            $table->string('prefecture', 50)->nullable()->comment('都道府県');
            $table->string('city', 100)->nullable()->comment('市区町村');
            $table->decimal('latitude', 10, 8)->nullable()->comment('緯度');
            $table->decimal('longitude', 11, 8)->nullable()->comment('経度');
            
            $table->time('arrival_time')->nullable()->comment('到着時刻');
            $table->time('departure_time')->nullable()->comment('出発時刻');
            $table->integer('stay_duration_minutes')->nullable()->comment('滞在時間');
            
            $table->decimal('distance_from_previous_km', 10, 2)->nullable();
            $table->integer('travel_time_from_previous_min')->nullable();
            
            $table->string('contact_name', 100)->nullable()->comment('担当者名');
            $table->string('contact_phone', 20)->nullable()->comment('連絡先電話番号');
            $table->text('notes')->nullable()->comment('備考');
            
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('route_id')->references('id')->on('quotation_routes')->onDelete('cascade');
            
            $table->index(['route_id', 'sequence_order']);
            $table->index('location_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_route_locations');
    }
};
