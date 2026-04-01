<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeMobileInfoTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('employee_mobile_info', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->bigInteger('employee_id');
      $table->string('device_type')->nullable();
      $table->string('owner')->nullable();
      $table->string('tel')->nullable();
      $table->string('android_id')->nullable();
      $table->string('imei_number')->nullable();
      $table->string('model_name')->nullable();
      $table->string('updated_column')->nullable();
      $table->string('connected_at')->nullable();
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
    Schema::dropIfExists('employee_mobile_info');
  }
}
