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
        Schema::create('employee_licenses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id');
            $table->string('license_number')->nullable();
            $table->string('license_type')->nullable();
            $table->date('license_issue_date')->nullable();
            $table->date('license_expiration_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_licenses');
    }
};
