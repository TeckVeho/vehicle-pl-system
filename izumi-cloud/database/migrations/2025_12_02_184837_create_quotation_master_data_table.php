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
        Schema::create('quotation_master_data', function (Blueprint $table) {
            $table->id();
            $table->string('tonnage', 50)->unique();
            $table->decimal('car_inspection_price', 10, 2)->default(264000);
            $table->decimal('regular_inspection_price', 10, 2)->default(22000);
            $table->decimal('tire_price', 10, 2)->default(50000);
            $table->decimal('oil_change_price', 10, 2)->default(20000);
            $table->decimal('fuel_unit_price', 5, 2)->default(5.00);
            $table->timestamps();
            
            $table->index('tonnage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_master_data');
    }
};
