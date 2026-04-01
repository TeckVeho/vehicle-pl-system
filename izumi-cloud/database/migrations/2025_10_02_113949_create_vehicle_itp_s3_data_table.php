<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicle_itp_s3_data', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('s3_files_id');
            $table->integer('vehicle_id');
            $table->string('vehicle_identification_number')->nullable();
            $table->string('no_number_plate')->nullable();
            $table->dateTime('start_date_time')->nullable();
            $table->dateTime('end_date_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_itp_s3_data');
    }
};
