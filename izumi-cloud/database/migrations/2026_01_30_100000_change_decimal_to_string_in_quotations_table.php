<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop indexes trước khi change type (vì string không thể có index)
        $existingIndexes = $this->getQuotationIndexes();

        Schema::table('quotations', function (Blueprint $table) use ($existingIndexes) {
            if (in_array('quotations_loading_location_index', $existingIndexes)) {
                $table->dropIndex(['loading_location']);
            }
            if (in_array('quotations_delivery_location_index', $existingIndexes)) {
                $table->dropIndex(['delivery_location']);
            }
            if (in_array('quotations_return_location_index', $existingIndexes)) {
                $table->dropIndex(['return_location']);
            }
            if (in_array('quotations_monthly_total_index', $existingIndexes)) {
                $table->dropIndex(['monthly_total']);
            }
        });

        Schema::table('quotations', function (Blueprint $table) {
            // Chuyển các trường decimal sang string
            $table->string('basic_hours')->nullable()->change();
            $table->string('night_hours')->nullable()->change();
            $table->string('overtime_hours')->nullable()->change();
            $table->string('night_total')->nullable()->change();
            $table->string('calc_working_hours')->nullable()->change();
            $table->string('overtime_total')->nullable()->change();
            $table->string('daily_rate')->nullable()->change();
            $table->string('hourly_wage')->nullable()->change();
            $table->string('working_days')->nullable()->change();
            $table->string('monthly_salary')->nullable()->change();
            $table->string('loading_location')->nullable()->change();
            $table->string('delivery_location')->nullable()->change();
            $table->string('return_location')->nullable()->change();
            $table->string('vehicle_price')->nullable()->change();
            $table->string('vehicle_lease')->nullable()->change();
            $table->string('lease_years')->nullable()->change();
            $table->string('residual_value_rate')->nullable()->change();
            $table->string('interest_rate')->nullable()->change();
            $table->string('installments')->nullable()->change();
            $table->string('calc_acquisition_tax')->nullable()->change();
            $table->string('vehicle_weight_tax')->nullable()->change();
            $table->string('automobile_tax')->nullable()->change();
            $table->string('insurance')->nullable()->change();
            $table->string('compulsory_insurance')->nullable()->change();
            $table->string('cargo_insurance')->nullable()->change();
            $table->string('daily_distance')->nullable()->change();
            $table->string('tire_replace_distance')->nullable()->change();
            $table->string('oil_replace_distance')->nullable()->change();
            $table->string('fuel_efficiency')->nullable()->change();
            $table->string('daily_highway_fee')->nullable()->change();
            $table->string('other_repair_costs')->nullable()->change();
            $table->string('profit_margin')->nullable()->change();
            $table->string('monthly_cargo_insurance')->nullable()->change();
            $table->string('total_vehicle_costs')->nullable()->change();
            $table->string('calc_total_taxes')->nullable()->change();
            $table->string('calc_vehicle_depreciation')->nullable()->change();
            $table->string('calc_benefits')->nullable()->change();
            $table->string('calc_legal_inspection')->nullable()->change();
            $table->string('calc_total_personnel_cost')->nullable()->change();
            $table->string('calc_tire_cost')->nullable()->change();
            $table->string('calc_inspection_fee')->nullable()->change();
            $table->string('total_delivery_cost')->nullable()->change();
            $table->string('calc_total_variable_cost')->nullable()->change();
            $table->string('calc_oil_cost')->nullable()->change();
            $table->string('calc_monthly_highway_fee')->nullable()->change();
            $table->string('monthly_total')->nullable()->change();
            $table->string('calc_fuel_cost')->nullable()->change();
            $table->string('calc_repair_cost')->nullable()->change();
            $table->string('gross_profit')->nullable()->change();

            // Thêm trường mới - checkbox 往復 (2 chiều) cho phí cao tốc
            $table->boolean('tow_way_highway')->default(false)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            // Xóa trường mới
            $table->dropColumn('tow_way_highway');

            // Chuyển lại các trường string về decimal
            $table->decimal('basic_hours', 50)->nullable()->change();
            $table->decimal('night_hours', 50)->nullable()->change();
            $table->decimal('overtime_hours', 50)->nullable()->change();
            $table->decimal('night_total', 50)->nullable()->change();
            $table->decimal('calc_working_hours', 50)->nullable()->change();
            $table->decimal('overtime_total', 50)->nullable()->change();
            $table->decimal('daily_rate', 50)->nullable()->change();
            $table->decimal('hourly_wage', 50)->nullable()->change();
            $table->decimal('working_days', 50)->nullable()->change();
            $table->decimal('monthly_salary', 50)->nullable()->change();
            $table->decimal('loading_location', 50)->nullable()->change();
            $table->decimal('delivery_location', 50)->nullable()->change();
            $table->decimal('return_location', 50)->nullable()->change();
            $table->decimal('vehicle_price', 50)->nullable()->change();
            $table->decimal('vehicle_lease', 50)->nullable()->change();
            $table->decimal('lease_years', 50)->nullable()->change();
            $table->decimal('residual_value_rate', 50)->nullable()->change();
            $table->decimal('interest_rate', 50)->nullable()->change();
            $table->decimal('installments', 50)->nullable()->change();
            $table->decimal('calc_acquisition_tax', 50)->nullable()->change();
            $table->decimal('vehicle_weight_tax', 50)->nullable()->change();
            $table->decimal('automobile_tax', 50)->nullable()->change();
            $table->decimal('insurance', 50)->nullable()->change();
            $table->decimal('compulsory_insurance', 50)->nullable()->change();
            $table->decimal('cargo_insurance', 50)->nullable()->change();
            $table->decimal('daily_distance', 50)->nullable()->change();
            $table->decimal('tire_replace_distance', 50)->nullable()->change();
            $table->decimal('oil_replace_distance', 50)->nullable()->change();
            $table->decimal('fuel_efficiency', 50)->nullable()->change();
            $table->decimal('daily_highway_fee', 50)->nullable()->change();
            $table->decimal('other_repair_costs', 50)->nullable()->change();
            $table->decimal('profit_margin', 50)->nullable()->change();
            $table->decimal('monthly_cargo_insurance', 15, 2)->nullable()->change();
            $table->decimal('total_vehicle_costs', 15, 2)->nullable()->change();
            $table->decimal('calc_total_taxes', 15, 2)->nullable()->change();
            $table->decimal('calc_vehicle_depreciation', 15, 2)->nullable()->change();
            $table->decimal('calc_benefits', 15, 2)->nullable()->change();
            $table->decimal('calc_legal_inspection', 15, 2)->nullable()->change();
            $table->decimal('calc_total_personnel_cost', 15, 2)->nullable()->change();
            $table->decimal('calc_tire_cost', 15, 2)->nullable()->change();
            $table->decimal('calc_inspection_fee', 15, 2)->nullable()->change();
            $table->decimal('total_delivery_cost', 15, 2)->nullable()->change();
            $table->decimal('calc_total_variable_cost', 15, 2)->nullable()->change();
            $table->decimal('calc_oil_cost', 15, 2)->nullable()->change();
            $table->decimal('calc_monthly_highway_fee', 15, 2)->nullable()->change();
            $table->decimal('monthly_total', 15, 2)->nullable()->change();
            $table->decimal('calc_fuel_cost', 15, 2)->nullable()->change();
            $table->decimal('calc_repair_cost', 15, 2)->nullable()->change();
            $table->decimal('gross_profit', 15, 2)->nullable()->change();
        });

        // Add lại indexes sau khi đổi về decimal (chỉ nếu chưa tồn tại)
        $existingIndexes = $this->getQuotationIndexes();

        Schema::table('quotations', function (Blueprint $table) use ($existingIndexes) {
            if (! in_array('quotations_loading_location_index', $existingIndexes)) {
                $table->index('loading_location');
            }
            if (! in_array('quotations_delivery_location_index', $existingIndexes)) {
                $table->index('delivery_location');
            }
            if (! in_array('quotations_return_location_index', $existingIndexes)) {
                $table->index('return_location');
            }
            if (! in_array('quotations_monthly_total_index', $existingIndexes)) {
                $table->index('monthly_total');
            }
        });
    }

    protected function getQuotationIndexes(): array
    {
        $targetIndexes = [
            'quotations_loading_location_index',
            'quotations_delivery_location_index',
            'quotations_return_location_index',
            'quotations_monthly_total_index',
        ];

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $indexes = DB::select("SHOW INDEX FROM quotations WHERE Key_name IN ('quotations_loading_location_index', 'quotations_delivery_location_index', 'quotations_return_location_index', 'quotations_monthly_total_index')");

            return collect($indexes)->pluck('Key_name')->unique()->values()->toArray();
        }

        if ($driver === 'sqlite') {
            $indexes = DB::select("PRAGMA index_list('quotations')");

            return collect($indexes)
                ->pluck('name')
                ->filter(fn (string $name): bool => in_array($name, $targetIndexes, true))
                ->unique()
                ->values()
                ->toArray();
        }

        return [];
    }
};
