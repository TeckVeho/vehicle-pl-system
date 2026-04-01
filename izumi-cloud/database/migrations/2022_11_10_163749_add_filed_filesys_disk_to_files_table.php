<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFiledFilesysDiskToFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('files', function (Blueprint $table) {
            $table->string('file_url',500)->after('file_size')->nullable();
            $table->string('file_sys_disk')->after('file_size')->nullable()->comment('"public", "ftp", "sftp", "s3"');
            $table->timestamp('expired_at')->nullable();
            $table->index('file_sys_disk');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('files', function (Blueprint $table) {
            //
        });
    }
}
