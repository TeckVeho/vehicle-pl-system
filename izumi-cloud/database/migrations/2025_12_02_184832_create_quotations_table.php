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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('author_id');
            $table->unsignedBigInteger('tonnage_id');
            
            $table->decimal('basic_hours', 50)->nullable();
            $table->decimal('night_hours', 50)->nullable();
            $table->decimal('overtime_hours', 50)->nullable();
            $table->decimal('night_total', 50)->nullable();
            $table->decimal('calc_working_hours', 50)->nullable();
            $table->decimal('overtime_total', 50)->nullable();
            $table->decimal('daily_rate', 50)->nullable();
            $table->decimal('hourly_wage', 50)->nullable();
            $table->decimal('working_days', 50)->nullable();
            $table->decimal('monthly_salary', 50)->nullable();
            $table->string('start_time', 10)->nullable();
            $table->string('end_time', 10)->nullable();
            $table->string('loading_time', 10)->nullable();
            $table->string('unloading_time', 10)->nullable();
            $table->string('working_hours', 50)->nullable();
            $table->string('break_hours', 50)->nullable();
            $table->decimal('loading_location', 50)->nullable();
            $table->decimal('delivery_location', 50)->nullable();
            $table->decimal('return_location', 50)->nullable();
            $table->decimal('vehicle_price', 50)->nullable();
            $table->decimal('vehicle_lease', 50)->nullable();
            $table->decimal('lease_years', 50)->nullable();
            $table->decimal('residual_value_rate', 50)->nullable();
            $table->decimal('interest_rate', 50)->nullable();
            $table->decimal('installments', 50)->nullable();
            $table->decimal('calc_acquisition_tax', 50)->nullable();
            $table->decimal('vehicle_weight_tax', 50)->nullable();
            $table->decimal('automobile_tax', 50)->nullable();
            $table->decimal('insurance', 50)->nullable();
            $table->decimal('compulsory_insurance', 50)->nullable();
            $table->decimal('cargo_insurance', 50)->nullable();
            $table->decimal('daily_distance', 50)->nullable();
            $table->decimal('tire_replace_distance', 50)->nullable();
            $table->decimal('oil_replace_distance', 50)->nullable();
            $table->decimal('fuel_efficiency', 50)->nullable();
            $table->decimal('daily_highway_fee', 50)->nullable();
            $table->decimal('other_repair_costs', 50)->nullable();
            $table->decimal('profit_margin', 50)->nullable();
            $table->decimal('monthly_cargo_insurance', 15, 2)->nullable();
            $table->decimal('total_vehicle_costs', 15, 2)->nullable();
            $table->decimal('calc_total_taxes', 15, 2)->nullable();
            $table->decimal('calc_vehicle_depreciation', 15, 2)->nullable();
            $table->decimal('calc_benefits', 15, 2)->nullable();
            $table->decimal('calc_legal_inspection', 15, 2)->nullable();
            $table->decimal('calc_total_personnel_cost', 15, 2)->nullable();
            $table->decimal('calc_tire_cost', 15, 2)->nullable();
            $table->decimal('calc_inspection_fee', 15, 2)->nullable();
            $table->decimal('total_delivery_cost', 15, 2)->nullable();
            $table->decimal('calc_total_variable_cost', 15, 2)->nullable();
            $table->decimal('calc_oil_cost', 15, 2)->nullable();
            $table->decimal('calc_monthly_highway_fee', 15, 2)->nullable();
            $table->decimal('monthly_total', 15, 2)->nullable();
            $table->decimal('calc_fuel_cost', 15, 2)->nullable();
            $table->decimal('calc_repair_cost', 15, 2)->nullable();
            $table->decimal('gross_profit', 15, 2)->nullable();
            
            $table->timestamps();
            $table->index('author_id');
            $table->index('created_at');
            $table->index('title');
            $table->index('monthly_total');
            $table->index('loading_location');
            $table->index('delivery_location');
            $table->index('return_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
