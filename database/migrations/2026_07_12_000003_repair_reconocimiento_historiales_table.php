<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repara instalaciones donde la migración de mejora del módulo quedó
     * registrada, pero la tabla de historial no llegó a crearse.
     */
    public function up(): void
    {
        if (Schema::hasTable('reconocimiento_historiales')) {
            return;
        }

        Schema::create('reconocimiento_historiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconocimiento_id')
                ->constrained('reconocimientos')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('accion');
            $table->text('descripcion')->nullable();
            $table->json('cambios')->nullable();
            $table->timestamps();
        });
    }

    /**
     * No eliminamos la tabla al revertir porque puede contener el historial
     * válido de reconocimientos creado antes de esta migración de reparación.
     */
    public function down(): void
    {
        // Intencionalmente vacío para proteger la bitácora existente.
    }
};
