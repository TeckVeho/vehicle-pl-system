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
        Schema::table('departments', function (Blueprint $table) {
            $table->string('chief_operations_manager')->nullable()->after('operations_manager_assistant');
            $table->dropColumn('maintenance_manager_phone_number');
            $table->json('maintenance_manager_appointment')->nullable()->after('chief_operations_manager')->change();
            $table->json('operations_manager_appointment')->nullable()->after('maintenance_manager_appointment')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('chief_operations_manager');
            $table->string('maintenance_manager_phone_number')->nullable()->after('maintenance_manager_assistant');
            $table->string('maintenance_manager_appointment')->nullable()->change();
            $table->string('operations_manager_appointment')->nullable()->change();
        });
    }
};
