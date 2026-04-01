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
        Schema::create('news_letters', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('status')->default(0)->comment('0: preview, 1: publish');
            $table->integer('position')->default(0);
            $table->bigInteger('file_id')->nullable();
            $table->string('year')->nullable();
            $table->string('month')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_letters');
    }
};
