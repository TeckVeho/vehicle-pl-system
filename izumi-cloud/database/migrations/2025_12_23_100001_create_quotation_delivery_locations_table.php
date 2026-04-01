<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotation_delivery_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id');
            $table->string('location_name', 255);
            $table->integer('sequence_order');
            $table->timestamps();
            
            $table->index('quotation_id');
            $table->foreign('quotation_id')
                  ->references('id')
                  ->on('quotations')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_delivery_locations');
    }
};

