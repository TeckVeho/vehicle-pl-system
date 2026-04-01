<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Change thinking_process from JSON to TEXT to store single-sentence string (Issue #752).
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('quotation_routes', function (Blueprint $table): void {
                $table->text('thinking_process')->nullable()->change();
            });

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE quotation_routes MODIFY thinking_process TEXT NULL');
        } else {
            DB::statement('ALTER TABLE quotation_routes ALTER COLUMN thinking_process TYPE TEXT USING thinking_process::TEXT');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('quotation_routes', function (Blueprint $table): void {
                $table->json('thinking_process')->nullable()->change();
            });

            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE quotation_routes MODIFY thinking_process JSON NULL');
        } else {
            DB::statement('ALTER TABLE quotation_routes ALTER COLUMN thinking_process TYPE JSON USING thinking_process::JSON');
        }
    }
};
