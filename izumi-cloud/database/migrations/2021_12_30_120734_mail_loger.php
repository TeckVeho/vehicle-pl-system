<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MailLoger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_logs', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->string('data_name')->nullable();
            $table->string('from_name')->nullable();
            $table->string('to_name')->nullable();
            $table->string('supervior_email')->nullable();
            $table->json('exception')->nullable();
            $table->string('seding_status')->default('waiting');
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
        //
    }
}
