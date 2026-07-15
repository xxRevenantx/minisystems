<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('etiqueta_alumnos', function (Blueprint $table): void {
            $table->string('generacion', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('etiqueta_alumnos')
            ->whereNull('generacion')
            ->update(['generacion' => 'SIN GENERACIÓN']);

        Schema::table('etiqueta_alumnos', function (Blueprint $table): void {
            $table->string('generacion', 100)->nullable(false)->change();
        });
    }
};
