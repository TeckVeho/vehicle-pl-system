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
        Schema::table('linework_bot_messages', function (Blueprint $table) {
            $table->string('message_en')->nullable()->after('message');
            $table->string('message_zh')->nullable()->after('message_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('linework_bot_messages', function (Blueprint $table) {
            $table->dropColumn('message_en');
            $table->dropColumn('message_zh');
        });
    }
};
