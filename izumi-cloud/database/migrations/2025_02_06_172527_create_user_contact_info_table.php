<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserContactInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_contact_info', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_contact_id')->unsigned();
            $table->string('urgent_contact_name')->nullable();
            $table->string('urgent_contact_relation')->nullable();
            $table->string('urgent_contact_tel')->nullable();
            $table->integer('group')->default(0);
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
        Schema::dropIfExists('user_contact_info');
    }
}
