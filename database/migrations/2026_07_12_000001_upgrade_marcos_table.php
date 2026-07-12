<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marcos', function (Blueprint $table) {
            $table->string('descripcion', 500)->nullable()->change();
            $table->string('nombre')->nullable()->after('id');
            $table->string('categoria')->default('General')->after('descripcion');
            $table->boolean('activo')->default(true)->after('categoria');

            $table->string('marco_desktop')->nullable()->after('marco');
            $table->string('marco_mobile')->nullable()->after('marco_desktop');

            $table->unsignedInteger('ancho_desktop')->nullable()->after('marco_mobile');
            $table->unsignedInteger('alto_desktop')->nullable()->after('ancho_desktop');
            $table->unsignedInteger('ancho_mobile')->nullable()->after('alto_desktop');
            $table->unsignedInteger('alto_mobile')->nullable()->after('ancho_mobile');

            $table->string('formato_desktop', 20)->nullable()->after('alto_mobile');
            $table->string('formato_mobile', 20)->nullable()->after('formato_desktop');
            $table->boolean('transparencia_desktop')->nullable()->after('formato_mobile');
            $table->boolean('transparencia_mobile')->nullable()->after('transparencia_desktop');

            $table->json('tags')->nullable()->after('transparencia_mobile');
            $table->text('notas')->nullable()->after('tags');
            $table->unsignedInteger('orden')->default(0)->after('notas');
            $table->unsignedBigInteger('usos')->default(0)->after('orden');
            $table->timestamp('ultimo_uso_at')->nullable()->after('usos');
            $table->softDeletes();

            $table->index(['activo', 'categoria']);
            $table->index('orden');
        });

        // Conserva los marcos creados con la estructura anterior.
        DB::table('marcos')->orderBy('id')->each(function ($marco): void {
            DB::table('marcos')->where('id', $marco->id)->update([
                'nombre' => $marco->descripcion ?: 'Marco '.$marco->id,
                'marco_desktop' => $marco->marco,
                'orden' => $marco->id,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('marcos', function (Blueprint $table) {
            $table->string('descripcion')->nullable(false)->change();
            $table->dropIndex(['activo', 'categoria']);
            $table->dropIndex(['orden']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'nombre',
                'categoria',
                'activo',
                'marco_desktop',
                'marco_mobile',
                'ancho_desktop',
                'alto_desktop',
                'ancho_mobile',
                'alto_mobile',
                'formato_desktop',
                'formato_mobile',
                'transparencia_desktop',
                'transparencia_mobile',
                'tags',
                'notas',
                'orden',
                'usos',
                'ultimo_uso_at',
            ]);
        });
    }
};
