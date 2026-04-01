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
        Schema::table('movie_watching', function (Blueprint $table) {
            $table->integer('export_flag')->default(0)->after('is_like_list');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movie_watching', function (Blueprint $table) {
            $table->dropColumn('export_flag');
        });
    }
};
