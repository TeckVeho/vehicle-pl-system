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
        Schema::create('maintenance_vehicle_pdf_history', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vehicle_maintenance_cost_id');
            $table->bigInteger('user_code');
            $table->bigInteger('file_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_vehicle_pdf_history');
    }
};
