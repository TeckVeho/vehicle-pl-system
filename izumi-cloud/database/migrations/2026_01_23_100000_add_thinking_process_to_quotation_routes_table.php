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
        Schema::table('quotation_routes', function (Blueprint $table) {
            $table->json('thinking_process')->nullable()->after('compliance_note')->comment('AI thinking process với route_strategy, calculation_basis, workload_analysis, compliance_reasoning, schedule_summary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_routes', function (Blueprint $table) {
            $table->dropColumn('thinking_process');
        });
    }
};
