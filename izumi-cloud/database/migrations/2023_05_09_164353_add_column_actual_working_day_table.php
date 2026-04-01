<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnActualWorkingDayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_timesheet_data', function (Blueprint $table) {
            $table->renameColumn('actual_wh', 'actual_working_day');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_timesheet_data', function (Blueprint $table) {
            $table->renameColumn('actual_working_day', 'actual_wh');
        });
    }
}
