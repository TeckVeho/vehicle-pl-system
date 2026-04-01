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
        Schema::create('google_auth_logs', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('name')->nullable();
            $table->string('google_id')->nullable();
            $table->string('picture')->nullable();
            $table->enum('action', ['login', 'logout', 'token_refresh', 'token_expired'])->default('login');
            $table->json('token_info')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['email', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_auth_logs');
    }
};
