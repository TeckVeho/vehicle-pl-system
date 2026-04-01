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
        Schema::create('quotation_routes', function (Blueprint $table) {
            $table->id();

            $table->string('route_code', 50)->unique()->comment('QR-YYYYMMDD-XXX');
            $table->unsignedBigInteger('user_id')->comment('User thực hiện tính toán');
            $table->unsignedBigInteger('quotation_id')->nullable()->comment('FK đến bảng quotations');

            $table->string('title', 500)->nullable()->comment('Tiêu đề');
            $table->string('pickup_location', 500)->comment('積地 - Điểm bốc hàng')->nullable();
            $table->string('delivery_location', 500)->comment('届け地 - Điểm giao hàng')->nullable();
            $table->string('return_location', 500)->comment('帰社地 - Điểm về')->nullable();
            $table->time('start_time')->comment('運行開始時間')->nullable();
            $table->string('vehicle_type', 50)->default('4t')->comment('車両区分');
            $table->integer('loading_time_minutes')->default(60)->comment('積み込み作業時間');
            $table->integer('unloading_time_minutes')->default(60)->comment('荷下ろし作業時間');
            $table->integer('user_break_time_minutes')->nullable()->comment('休憩時間指定');

            $table->decimal('total_distance_km', 10, 2)->nullable()->comment('総距離');
            $table->time('estimated_end_time')->nullable()->comment('終了予定時刻');
            $table->boolean('date_change')->default(false)->comment('日付変更フラグ');

            $table->decimal('total_duty_time_hours', 5, 2)->nullable()->comment('拘束時間');
            $table->decimal('actual_working_hours', 5, 2)->nullable()->comment('実労働時間');
            $table->integer('total_driving_time_minutes')->nullable()->comment('総運転時間');
            $table->integer('total_handling_time_minutes')->nullable()->comment('荷役時間合計');
            $table->integer('total_break_time_minutes')->nullable()->comment('休憩時間合計');

            $table->decimal('highway_fee', 12, 2)->default(0)->comment('高速道路料金合計');
            $table->decimal('fuel_cost', 12, 2)->default(0)->comment('燃料費');
            $table->decimal('estimated_total_cost', 12, 2)->default(0)->comment('総費用見積');

            $table->boolean('is_compliant')->default(true)->comment('法令基準を満たすか');
            $table->text('applied_rule')->nullable()->comment('適用したルールの説明');

            $table->string('ai_model_used', 50)->nullable()->comment('AI model');
            $table->integer('calculation_duration_seconds')->nullable()->comment('計算時間');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable()->comment('エラーメッセージ');
            $table->text('notes')->nullable()->comment('備考');

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('route_code');
            $table->index('status');
            $table->index('quotation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_routes');
    }
};
