<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quotation_route_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('route_id')->comment('FK đến quotation_routes');
            
            $table->enum('file_type', ['request', 'response'])->comment('Loại file');
            $table->string('file_path', 500)->comment('Đường dẫn file');
            $table->string('file_name', 255)->comment('Tên file');
            $table->unsignedBigInteger('file_size')->nullable()->comment('Kích thước (bytes)');
            $table->string('mime_type', 50)->default('application/json');
            
            $table->string('storage_disk', 50)->default('local')->comment('local, s3, etc');
            $table->boolean('is_archived')->default(false)->comment('Đã archive chưa');
            $table->timestamp('archived_at')->nullable()->comment('Thời gian archive');
            
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('route_id')->references('id')->on('quotation_routes')->onDelete('cascade');
            
            $table->index(['route_id', 'file_type']);
            $table->index('created_at');
            $table->index(['is_archived', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_route_files');
    }
};
