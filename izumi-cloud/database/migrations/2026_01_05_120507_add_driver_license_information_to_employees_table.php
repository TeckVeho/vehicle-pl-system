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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('driver_license_information')->nullable()->after('driver_license_upload_file_flag');
            $table->dropColumn('contact_phone_number_company');
            $table->dropColumn('contact_phone_number_personal');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('driver_license_information');
            $table->string('contact_phone_number_company')->nullable();
            $table->string('contact_phone_number_personal')->nullable();
        });
    }
};
