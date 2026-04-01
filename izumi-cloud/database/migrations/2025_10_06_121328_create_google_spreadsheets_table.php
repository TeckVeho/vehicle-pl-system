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
        Schema::create('google_spreadsheets', function (Blueprint $table) {
            $table->id();
            $table->string('spreadsheet_id');
            $table->string('spreadsheet_name');
            $table->string('folder_id')->nullable();
            $table->integer('year');
            $table->timestamp('last_sync_at')->nullable();
            $table->enum('sync_status', ['pending', 'syncing', 'completed', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_spreadsheets');
    }
};
