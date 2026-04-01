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
            $table->integer('beginner_driver_training_classroom')
                ->default(0)->after('fixed_salary')->comment('0: 完了、, 1: 未完了');
            $table->integer('beginner_driver_training_practical')
                ->default(0)->after('beginner_driver_training_classroom')->comment('0: 完了、,1: 未完了');
            $table->integer('driver_license_upload_file_flag')->default(0)->after('beginner_driver_training_practical')->comment('0: 未アップロード、1: アップロード済み');
            $table->integer('driving_record_certificate_upload_file_flag')->default(0)->after('driver_license_upload_file_flag')->comment('0: 未アップロード、1: アップロード済み');
            $table->integer('health_examination_results_upload_file_flag')->default(0)->after('driving_record_certificate_upload_file_flag')->comment('0: 未アップロード、1: アップロード済み');
            $table->integer('aptitude_assessment_form_upload_file_flag')->default(0)->after('health_examination_results_upload_file_flag')->comment('0: 未アップロード、1: アップロード済み');
            $table->string('name_in_furigana')->nullable()->after('name');
            $table->date('date_of_election')->nullable()->after('name_in_furigana');
            $table->string('address')->nullable()->after('date_of_election');
            $table->string('contact_phone_number_company')->nullable()->after('address');
            $table->string('contact_phone_number_personal')->nullable()->after('contact_phone_number_company');
            $table->date('aptitude_test_date')->nullable()->after('contact_phone_number_personal');
            $table->date('health_checkup_date')->nullable()->after('aptitude_test_date');
            $table->text('previous_employment_history')->nullable()->after('health_checkup_date');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('beginner_driver_training_classroom');
            $table->dropColumn('beginner_driver_training_practical');
            $table->dropColumn('driver_license_upload_file_flag');
            $table->dropColumn('driving_record_certificate_upload_file_flag');
            $table->dropColumn('health_examination_results_upload_file_flag');
            $table->dropColumn('aptitude_assessment_form_upload_file_flag');
            $table->dropColumn('name_in_furigana');
            $table->dropColumn('date_of_election');
            $table->dropColumn('address');
            $table->dropColumn('contact_phone_number_company');
            $table->dropColumn('contact_phone_number_personal');
            $table->dropColumn('aptitude_test_date');
            $table->dropColumn('health_checkup_date');
            $table->dropColumn('previous_employment_history');
        });
    }
};
