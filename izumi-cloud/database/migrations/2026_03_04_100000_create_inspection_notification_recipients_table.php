<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Issue #810: 例外通知者マスタ（拠点 × ユーザ）
     */
    public function up(): void
    {
        Schema::create('inspection_notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id');
            $table->bigInteger('user_id'); // users.id is bigInteger (signed) in create_users_table
            $table->timestamps();

            $table->unique(['department_id', 'user_id']);
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_notification_recipients');
    }
};
