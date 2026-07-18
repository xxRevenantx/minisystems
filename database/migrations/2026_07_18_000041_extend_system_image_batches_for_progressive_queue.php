<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_image_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('system_image_batches', 'zip_parts')) {
                $table->json('zip_parts')->nullable()->after('zip_path');
            }
        });

        Schema::table('system_image_items', function (Blueprint $table) {
            if (! Schema::hasColumn('system_image_items', 'settings')) {
                $table->json('settings')->nullable()->after('orientation');
            }
        });
    }

    public function down(): void
    {
        Schema::table('system_image_items', function (Blueprint $table) {
            if (Schema::hasColumn('system_image_items', 'settings')) {
                $table->dropColumn('settings');
            }
        });

        Schema::table('system_image_batches', function (Blueprint $table) {
            if (Schema::hasColumn('system_image_batches', 'zip_parts')) {
                $table->dropColumn('zip_parts');
            }
        });
    }
};
