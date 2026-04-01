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
        Schema::create('health_examination_results_file_history', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('file_id');
            $table->bigInteger('user_id');
            $table->bigInteger('employee_health_examination_results_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_examination_results_file_history');
    }
};
