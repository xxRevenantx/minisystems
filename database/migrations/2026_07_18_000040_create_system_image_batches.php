<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_image_batches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30)->default('uploading');
            $table->json('settings')->nullable();
            $table->unsignedInteger('total_files')->default(0);
            $table->unsignedInteger('uploaded_files')->default(0);
            $table->unsignedInteger('processed_files')->default(0);
            $table->unsignedInteger('completed_files')->default(0);
            $table->unsignedInteger('failed_files')->default(0);
            $table->unsignedBigInteger('bytes_total')->default(0);
            $table->unsignedBigInteger('bytes_uploaded')->default(0);
            $table->string('zip_status', 30)->default('pending');
            $table->string('zip_path')->nullable();
            $table->unsignedBigInteger('zip_size')->nullable();
            $table->text('zip_error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('processing_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('export_registered_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('system_image_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_image_batch_id')->constrained()->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->unsignedInteger('position');
            $table->string('client_fingerprint', 1000);
            $table->string('relative_path', 1000)->nullable();
            $table->string('original_name');
            $table->string('stored_name')->nullable();
            $table->string('source_path')->nullable();
            $table->string('output_name')->nullable();
            $table->string('output_path')->nullable();
            $table->string('mime', 100)->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('original_size')->default(0);
            $table->unsignedBigInteger('processed_size')->nullable();
            $table->unsignedInteger('original_width')->nullable();
            $table->unsignedInteger('original_height')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('orientation', 20)->nullable();
            $table->string('status', 30)->default('pending_upload');
            $table->json('warnings')->nullable();
            $table->text('error')->nullable();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['system_image_batch_id', 'position'], 'system_image_batch_position_unique');
            $table->index(['system_image_batch_id', 'status'], 'system_image_batch_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_image_items');
        Schema::dropIfExists('system_image_batches');
    }
};
