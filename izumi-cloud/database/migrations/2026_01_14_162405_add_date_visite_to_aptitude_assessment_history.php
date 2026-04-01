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
        Schema::table('aptitude_assessment_forms_file_history', function (Blueprint $table) {
            $table->date('date_of_visit')->nullable();
            $table->integer('type')->nullable();
        });

        Schema::table('health_examination_results_file_history', function (Blueprint $table) {
            $table->date('date_of_visit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aptitude_assessment_forms_file_history', function (Blueprint $table) {
            $table->dropColumn('date_of_visit');
            $table->dropColumn('type');
        });
        Schema::table('health_examination_results_file_history', function (Blueprint $table) {
            $table->dropColumn('date_of_visit');
        });
    }
};
