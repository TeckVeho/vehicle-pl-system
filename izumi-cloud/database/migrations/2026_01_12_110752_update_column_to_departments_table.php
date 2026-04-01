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
            $table->json('maintenance_manager_assistant')->nullable()->change();
            $table->json('operations_manager_assistant')->nullable()->change();
            $table->integer('g_mark_action_radio')->nullable()->after('g_mark_expiration_date')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('maintenance_manager_assistant')->nullable()->change();
            $table->string('operations_manager_assistant')->nullable()->change();
            $table->dropColumn('g_mark_action_radio');
        });
    }
};
