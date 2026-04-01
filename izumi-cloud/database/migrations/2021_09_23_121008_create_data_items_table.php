<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('data_connection_id')->nullable();
            $table->string('status', 255)->nullable()->comment('fail->success');
            $table->json('content')->nullable();
            $table->string('type')->nullable()->default("active");
            $table->bigInteger('file_id')->nullable();
            $table->bigInteger('who_uploaded')->unsigned()->default(0);
            $table->json('data_connection_history')->nullable();
            $table->json('msg_error')->nullable();
            $table->json('response_body')->nullable();
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
        // Schema::dropIfExists('data_items');
    }
}
