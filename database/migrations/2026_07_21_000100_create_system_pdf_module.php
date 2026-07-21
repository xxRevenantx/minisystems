<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pdf_permisos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('ver')->default(true);
            $table->boolean('procesar')->default(false);
            $table->boolean('descargar')->default(true);
            $table->boolean('eliminar')->default(false);
            $table->boolean('administrar')->default(false);
            $table->timestamps();
        });

        Schema::create('pdf_batches', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('operation', 30)->index();
            $table->string('status', 30)->default('uploading')->index();
            $table->json('settings')->nullable();
            $table->longText('secret')->nullable();
            $table->unsignedInteger('total_files')->default(0);
            $table->unsignedInteger('uploaded_files')->default(0);
            $table->unsignedInteger('processed_files')->default(0);
            $table->unsignedInteger('completed_files')->default(0);
            $table->unsignedInteger('failed_files')->default(0);
            $table->unsignedBigInteger('original_bytes')->default(0);
            $table->unsignedBigInteger('output_bytes')->default(0);
            $table->string('output_name')->nullable();
            $table->string('output_path', 1000)->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('pdf_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pdf_batch_id')->constrained('pdf_batches')->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->unsignedInteger('position');
            $table->string('client_fingerprint', 1000)->nullable();
            $table->string('original_name');
            $table->string('stored_name')->nullable();
            $table->string('source_path', 1000)->nullable();
            $table->string('mime', 120)->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('original_size')->default(0);
            $table->unsignedInteger('page_count')->nullable();
            $table->boolean('encrypted')->default(false);
            $table->string('status', 30)->default('pending_upload')->index();
            $table->string('output_name')->nullable();
            $table->string('output_path', 1000)->nullable();
            $table->unsignedBigInteger('output_size')->nullable();
            $table->json('result_files')->nullable();
            $table->json('thumbnails')->nullable();
            $table->json('warnings')->nullable();
            $table->longText('secret')->nullable();
            $table->text('error')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['pdf_batch_id', 'position']);
            $table->index(['pdf_batch_id', 'status']);
        });

        $now = now();
        $users = DB::table('users')->select('id')->orderBy('id')->get();

        foreach ($users as $user) {
            $admin = (int) $user->id === 1;

            DB::table('pdf_permisos')->insert([
                'user_id' => $user->id,
                'ver' => true,
                'procesar' => $admin,
                'descargar' => true,
                'eliminar' => $admin,
                'administrar' => $admin,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pdf_items');
        Schema::dropIfExists('pdf_batches');
        Schema::dropIfExists('pdf_permisos');
    }
};
