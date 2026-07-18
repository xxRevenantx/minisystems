<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            if (Schema::hasTable('image_optimizer_batches')) {
                DB::statement('ALTER TABLE image_optimizer_batches MODIFY total_files INT UNSIGNED NOT NULL DEFAULT 0');
                DB::statement('ALTER TABLE image_optimizer_batches MODIFY uploaded_files INT UNSIGNED NOT NULL DEFAULT 0');
                DB::statement('ALTER TABLE image_optimizer_batches MODIFY processed_files INT UNSIGNED NOT NULL DEFAULT 0');
                DB::statement('ALTER TABLE image_optimizer_batches MODIFY completed_files INT UNSIGNED NOT NULL DEFAULT 0');
                DB::statement('ALTER TABLE image_optimizer_batches MODIFY failed_files INT UNSIGNED NOT NULL DEFAULT 0');
            }

            if (Schema::hasTable('image_optimizer_items')) {
                DB::statement('ALTER TABLE image_optimizer_items MODIFY position INT UNSIGNED NOT NULL');
            }
        }
    }

    public function down(): void
    {
        // No se revierte a SMALLINT para no truncar lotes grandes ya creados.
    }
};
