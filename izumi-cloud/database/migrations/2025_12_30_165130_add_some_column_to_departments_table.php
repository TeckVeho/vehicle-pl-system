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
            $table->string('office_name')->nullable()->after('tel');
            $table->string('office_location')->nullable()->after('office_name');
            $table->string('office_area')->nullable()->after('office_location');
            $table->string('rest_room_area')->nullable()->after('office_area');
            $table->string('garage_location_1')->nullable()->after('rest_room_area');
            $table->string('garage_area_1')->nullable()->after('garage_location_1');
            $table->string('garage_location_2')->nullable()->after('garage_area_1');
            $table->string('garage_area_2')->nullable()->after('garage_location_2');
            $table->string('operations_manager_appointment')->nullable()->after('garage_area_2');
            $table->string('operations_manager_assistant')->nullable()->after('operations_manager_appointment');
            $table->string('maintenance_manager_appointment')->nullable()->after('operations_manager_assistant');
            $table->string('maintenance_manager_assistant')->nullable()->after('maintenance_manager_appointment');
            $table->string('maintenance_manager_phone_number')->nullable()->after('maintenance_manager_assistant');
            $table->string('maintenance_manager_fax_number')->nullable()->after('maintenance_manager_phone_number');
            $table->string('truck_association_membership_number')->nullable()->after('maintenance_manager_fax_number');
            $table->string('g_mark_number')->nullable()->after('truck_association_membership_number');
            $table->string('g_mark_expiration_date')->nullable()->after('g_mark_number');
            $table->integer('it_roll_call')->nullable()->after('g_mark_expiration_date')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('office_name');
            $table->dropColumn('office_location');
            $table->dropColumn('office_area');
            $table->dropColumn('rest_room_area');
            $table->dropColumn('garage_location_1');
            $table->dropColumn('garage_area_1');
            $table->dropColumn('garage_location_2');
            $table->dropColumn('garage_area_2');
            $table->dropColumn('operations_manager_appointment');
            $table->dropColumn('operations_manager_assistant');
            $table->dropColumn('maintenance_manager_appointment');
            $table->dropColumn('maintenance_manager_assistant');
            $table->dropColumn('maintenance_manager_phone_number');
            $table->dropColumn('maintenance_manager_fax_number');
            $table->dropColumn('truck_association_membership_number');
            $table->dropColumn('g_mark_number');
            $table->dropColumn('g_mark_expiration_date');
            $table->dropColumn('it_roll_call');
        });
    }
};