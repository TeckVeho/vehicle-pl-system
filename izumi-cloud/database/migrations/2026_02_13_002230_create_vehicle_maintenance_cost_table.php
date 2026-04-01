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
        Schema::create('vehicle_maintenance_costs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('type_text')->nullable();
            $table->date('scheduled_date')->nullable();
            $table->date('scheduled_date_display')->nullable();
            $table->integer('schedule_month')->nullable();
            $table->integer('schedule_year')->nullable();
            $table->date('maintained_date')->nullable();
            $table->date('maintained_date_display')->nullable();
            $table->bigInteger('vehicle_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance_costs');
    }
};
