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
        Schema::create('google_scripts', function (Blueprint $table) {
            $table->id();
            $table->string('spreadsheet_id')->index();
            $table->string('script_id')->unique();
            $table->string('status')->default('active');
            $table->json('script_info')->nullable(); // Lưu thông tin chi tiết về script
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();

            $table->index(['spreadsheet_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_scripts');
    }
};
