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
        Schema::create('s3_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('folder', 255)->nullable();
            $table->string('name', 255)->nullable();
            $table->string('path')->nullable();
            $table->integer('status')->default(0)->comment('0: pending, 1: process, 2: done');
            $table->string('last_modified')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('s3_files');
    }
};
