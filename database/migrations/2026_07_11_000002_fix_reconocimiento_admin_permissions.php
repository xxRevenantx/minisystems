<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasTable('reconocimiento_permisos')) {
            return;
        }

        $adminId = DB::table('users')->min('id');

        if (! $adminId) {
            return;
        }

        DB::table('reconocimiento_permisos')->updateOrInsert(
            ['user_id' => $adminId],
            [
                'ver' => true,
                'crear' => true,
                'editar' => true,
                'aprobar' => true,
                'descargar' => true,
                'cancelar' => true,
                'administrar' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        // No se revocan permisos en rollback para evitar bloquear al administrador.
    }
};
