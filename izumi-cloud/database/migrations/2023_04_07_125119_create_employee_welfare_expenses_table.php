<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeWelfareExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_welfare_expenses', function (Blueprint $table) {
            $table->bigInteger('employee_id')->unsigned();
            $table->double('welfare_expense')->default(0);
            $table->date('start_date')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_welfare_expenses');
    }
}
