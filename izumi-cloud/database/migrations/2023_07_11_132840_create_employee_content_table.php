<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_content', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id');
            $table->string('company_car', 20)->nullable();
            $table->string('etc_card', 20)->nullable();
            $table->string('fuel_card', 20)->nullable();
            $table->string('other', 200)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_content');
    }
}
