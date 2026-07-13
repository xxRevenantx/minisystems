<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etiqueta_permisos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('ver')->default(true);
            $table->boolean('crear')->default(false);
            $table->boolean('editar')->default(false);
            $table->boolean('eliminar')->default(false);
            $table->boolean('importar')->default(false);
            $table->boolean('descargar')->default(true);
            $table->boolean('administrar')->default(false);
            $table->timestamps();
        });

        Schema::create('etiqueta_plantillas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nombre');
            $table->string('nivel')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('fondo');
            $table->string('disk', 30)->default('public');
            $table->boolean('es_predeterminada')->default(false);
            $table->boolean('activo')->default(true);
            $table->json('configuracion')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['nivel', 'activo']);
            $table->index(['es_predeterminada', 'activo']);
        });

        Schema::create('etiqueta_alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('persona_id')->nullable()->constrained('personas')->nullOnDelete();
            $table->string('nombre');
            $table->string('apellido_paterno')->nullable();
            $table->string('apellido_materno')->nullable();
            $table->string('nivel', 100);
            $table->string('generacion', 100);
            $table->string('grado', 50)->nullable();
            $table->string('grupo', 50)->nullable();
            $table->boolean('activo')->default(true);
            $table->json('datos_extra')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['nivel', 'generacion']);
            $table->index(['grado', 'grupo']);
            $table->index(['activo', 'nombre']);
            $table->index(['apellido_paterno', 'apellido_materno']);
        });

        if (Schema::hasTable('users')) {
            $adminId = DB::table('users')->min('id');
            DB::table('users')->orderBy('id')->pluck('id')->each(function ($id) use ($adminId) {
                DB::table('etiqueta_permisos')->insert([
                    'user_id' => $id,
                    'ver' => true,
                    'crear' => $id === $adminId,
                    'editar' => $id === $adminId,
                    'eliminar' => $id === $adminId,
                    'importar' => $id === $adminId,
                    'descargar' => true,
                    'administrar' => $id === $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('etiqueta_alumnos');
        Schema::dropIfExists('etiqueta_plantillas');
        Schema::dropIfExists('etiqueta_permisos');
    }
};
