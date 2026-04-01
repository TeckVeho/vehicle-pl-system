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
        Schema::create('google_spreadsheet_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('spreadsheet_id');
            $table->string('sheet_id');
            $table->integer('department_id')->nullable();
            $table->string('title')->comment('is department name')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_spreadsheet_sheets');
    }
};
