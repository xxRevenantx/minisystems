<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('image_optimizer_batches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30)->default('uploading')->index();
            $table->json('settings');
            $table->unsignedSmallInteger('total_files')->default(0);
            $table->unsignedSmallInteger('uploaded_files')->default(0);
            $table->unsignedSmallInteger('processed_files')->default(0);
            $table->unsignedSmallInteger('completed_files')->default(0);
            $table->unsignedSmallInteger('failed_files')->default(0);
            $table->unsignedBigInteger('bytes_total')->default(0);
            $table->unsignedBigInteger('bytes_uploaded')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('export_registered_at')->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        Schema::create('image_optimizer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('image_optimizer_batch_id')
                ->constrained('image_optimizer_batches')
                ->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->unsignedSmallInteger('position');
            $table->string('client_fingerprint', 1000);
            $table->string('relative_path', 1000)->nullable();
            $table->string('original_name');
            $table->string('stored_name')->nullable();
            $table->string('source_path', 1000)->nullable();
            $table->string('output_name')->nullable();
            $table->string('output_path', 1000)->nullable();
            $table->string('mime', 100)->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('original_size')->default(0);
            $table->unsignedBigInteger('optimized_size')->nullable();
            $table->unsignedBigInteger('saved_bytes')->default(0);
            $table->unsignedInteger('original_width')->nullable();
            $table->unsignedInteger('original_height')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('format', 20)->nullable();
            $table->unsignedTinyInteger('quality')->nullable();
            $table->decimal('reduction', 8, 2)->nullable();
            $table->string('status', 30)->default('pending_upload')->index();
            $table->json('warnings')->nullable();
            $table->text('error')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['image_optimizer_batch_id', 'position'],
                'image_optimizer_items_batch_position_unique'
            );
            $table->index(
                ['image_optimizer_batch_id', 'status'],
                'image_optimizer_items_batch_status_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('image_optimizer_items');
        Schema::dropIfExists('image_optimizer_batches');
    }
};
