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
        Schema::create('hotline_setting_lwms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('client_secret')->nullable();
            $table->string('client_id')->nullable();
            $table->string('scope')->nullable();
            $table->string('response_type')->nullable();
            $table->string('state')->nullable();
            $table->string('bot_id')->nullable();
            $table->string('channel_id')->nullable();
            $table->string('app_url')->nullable();
            $table->string('service_account')->nullable();
            $table->string('private_key_path')->nullable();
            $table->string('environment')->default('dev'); // Added environment column
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotline_setting_lwms');
    }
};
