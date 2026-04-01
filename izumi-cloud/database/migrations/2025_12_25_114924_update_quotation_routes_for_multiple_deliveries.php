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
        Schema::table('quotation_routes', function (Blueprint $table) {
            $table->string('start_location', 500)->nullable()->after('user_id')->comment('出発地 - Điểm xuất phát');
            $table->json('delivery_locations')->nullable()->after('delivery_location')->comment('複数届け地 - Multiple delivery locations');
            $table->text('compliance_note')->nullable()->after('applied_rule')->comment('コンプライアンス注記 - Compliance note');
        });
        
        if (Schema::hasTable('quotation_route_segments')) {
            Schema::table('quotation_route_segments', function (Blueprint $table) {
                if (!Schema::hasColumn('quotation_route_segments', 'segment_type')) {
                    $table->string('segment_type', 50)->nullable()->after('segment_order')->comment('回送/実車 - Segment type');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_routes', function (Blueprint $table) {
            $table->dropColumn(['start_location', 'delivery_locations', 'compliance_note']);
        });
        
        if (Schema::hasTable('quotation_route_segments')) {
            Schema::table('quotation_route_segments', function (Blueprint $table) {
                if (Schema::hasColumn('quotation_route_segments', 'segment_type')) {
                    $table->dropColumn('segment_type');
                }
            });
        }
    }
};
