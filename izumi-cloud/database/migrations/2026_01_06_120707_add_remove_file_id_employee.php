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
        Schema::table('employee_aptitude_assessment_forms', function (Blueprint $table) {
            $table->dropColumn('file_id');
            $table->dropColumn('user_id');
        });
        Schema::table('employee_health_examination_results', function (Blueprint $table) {
            $table->dropColumn('file_id');
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_aptitude_assessment_forms', function (Blueprint $table) {
            $table->bigInteger('file_id')->nullable();
            $table->bigInteger('user_id')->nullable();
        });
        Schema::table('employee_health_examination_results', function (Blueprint $table) {
            $table->bigInteger('file_id')->nullable();
            $table->bigInteger('user_id')->nullable();
        });
    }
};
