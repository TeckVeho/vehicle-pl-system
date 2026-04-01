<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataConnectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_connections', function (Blueprint $table) {
            $table->bigIncrements('id')->autoIncrement();
            $table->string('name')->nullable();
            $table->bigInteger('from')->unsigned()->default(0);
            $table->bigInteger('to')->unsigned()->default(0);
            $table->string('type')->comment("active/passive");
            $table->string('frequency');
            $table->string('date_on')->nullable();
            $table->string('week_on')->nullable();
            $table->string('time_at')->nullable();
            $table->json('frequency_between')->nullable();
            $table->string('connection_frequency')->nullable();
            $table->string('connection_timing')->nullable();
            $table->dateTime('final_connect_time')->nullable();
            $table->string('final_status')->nullable()->comment("waiting->excluding->fail->success");
            $table->string('service_class_name')->nullable();
            $table->string('remark')->nullable()->default('');
            $table->boolean('is_import')->nullable()->default(false);
            $table->string('import_to_table')->nullable();
            $table->string('file_name_map')->nullable();
            $table->string('data_code')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_connections');
    }
}
