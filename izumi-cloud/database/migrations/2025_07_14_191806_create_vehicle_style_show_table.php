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
        Schema::create('vehicle_style_show', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('key')->nullable();
            $table->string('label')->nullable();
            $table->integer('position')->nullable();
            $table->boolean('is_deletable')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_display')->default(false);
            $table->boolean('is_selected')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_style_show');
    }
};
