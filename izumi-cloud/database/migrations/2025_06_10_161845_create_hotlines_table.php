<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHotlinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hotlines', function (Blueprint $table) {
            $table->id();
            $table->text('content')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('username')->nullable();
            $table->integer('contact_flag')->default(0);
            $table->integer('check_anonymous_flag')->default(0)->comment('0 = anonymous, 1 = not anonymous');
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
        Schema::dropIfExists('hotlines');
    }
}
