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
        Schema::table('vehicle_pdf_history', function (Blueprint $table) {
            $table->string('date_pdf')->nullable()->after('file_id');
            $table->string('date_json')->nullable()->after('date_pdf');
            $table->string('car_no')->nullable()->after('date_json');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicle_pdf_history', function (Blueprint $table) {
            $table->dropColumn('date');
            $table->dropColumn('car_no');
        });
    }
};
