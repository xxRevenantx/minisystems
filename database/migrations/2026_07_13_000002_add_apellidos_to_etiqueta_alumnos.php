<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('etiqueta_alumnos')) {
            return;
        }

        Schema::table('etiqueta_alumnos', function (Blueprint $table): void {
            if (! Schema::hasColumn('etiqueta_alumnos', 'apellido_paterno')) {
                $table->string('apellido_paterno')->nullable()->after('nombre');
            }

            if (! Schema::hasColumn('etiqueta_alumnos', 'apellido_materno')) {
                $table->string('apellido_materno')->nullable()->after('apellido_paterno');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('etiqueta_alumnos')) {
            return;
        }

        Schema::table('etiqueta_alumnos', function (Blueprint $table): void {
            $columnas = array_values(array_filter([
                Schema::hasColumn('etiqueta_alumnos', 'apellido_materno') ? 'apellido_materno' : null,
                Schema::hasColumn('etiqueta_alumnos', 'apellido_paterno') ? 'apellido_paterno' : null,
            ]));

            if ($columnas !== []) {
                $table->dropColumn($columnas);
            }
        });
    }
};
