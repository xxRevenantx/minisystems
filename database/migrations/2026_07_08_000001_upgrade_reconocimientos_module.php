<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reconocimiento_tipos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('titulo')->nullable();
            $table->text('descripcion');
            $table->string('destinatario_tipo')->default('alumno');
            $table->boolean('usa_lugar')->default(false);
            $table->json('niveles')->nullable();
            $table->foreignId('reconocimiento_imagen_id')->nullable()->constrained('reconocimiento_imagenes')->nullOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('reconocimiento_eventos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('categoria')->nullable();
            $table->date('fecha')->nullable();
            $table->string('lugar')->nullable();
            $table->string('nivel')->nullable();
            $table->string('ciclo_escolar')->nullable();
            $table->foreignId('reconocimiento_tipo_id')->nullable()->constrained('reconocimiento_tipos')->nullOnDelete();
            $table->foreignId('reconocimiento_imagen_id')->nullable()->constrained('reconocimiento_imagenes')->nullOnDelete();
            $table->string('estado')->default('activo');
            $table->text('observaciones')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('reconocimiento_imagenes', function (Blueprint $table) {
            $table->string('nombre')->nullable()->after('imagen');
            $table->string('orientacion')->default('horizontal')->after('descripcion');
            $table->json('configuracion')->nullable()->after('orientacion');
            $table->boolean('activo')->default(true)->after('configuracion');
        });

        Schema::table('directivos', function (Blueprint $table) {
            $table->string('firma')->nullable()->after('cargo');
            $table->string('sello')->nullable()->after('firma');
            $table->date('vigencia_inicio')->nullable()->after('sello');
            $table->date('vigencia_fin')->nullable()->after('vigencia_inicio');
            $table->json('niveles')->nullable()->after('vigencia_fin');
            $table->unsignedInteger('orden')->default(0)->after('niveles');
            $table->boolean('activo')->default(true)->after('orden');
        });

        Schema::table('reconocimientos', function (Blueprint $table) {
            $table->dropForeign(['reconocimiento_imagen_id']);
        });
        Schema::table('reconocimientos', function (Blueprint $table) {
            $table->foreign('reconocimiento_imagen_id')->references('id')->on('reconocimiento_imagenes')->nullOnDelete();
            $table->foreignId('reconocimiento_evento_id')->nullable()->after('id')->constrained('reconocimiento_eventos')->nullOnDelete();
            $table->foreignId('reconocimiento_tipo_id')->nullable()->after('reconocimiento_evento_id')->constrained('reconocimiento_tipos')->nullOnDelete();
            $table->foreignId('credencial_id')->nullable()->after('reconocimiento_tipo_id')->constrained('credenciales')->nullOnDelete();
            $table->string('destinatario_tipo')->default('externo')->after('credencial_id');
            $table->string('estado')->default('borrador')->after('fecha');
            $table->unsignedInteger('version')->default(1)->after('estado');
            $table->foreignId('duplicado_de_id')->nullable()->after('version')->constrained('reconocimientos')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->after('duplicado_de_id')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('generated_at')->nullable()->after('approved_at');
            $table->timestamp('delivered_at')->nullable()->after('generated_at');
            $table->string('delivery_method')->nullable()->after('delivered_at');
            $table->string('delivery_to')->nullable()->after('delivery_method');
            $table->text('delivery_notes')->nullable()->after('delivery_to');
            $table->text('cancel_reason')->nullable()->after('delivery_notes');
            $table->softDeletes();
        });

        Schema::create('reconocimiento_historiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconocimiento_id')->constrained('reconocimientos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion');
            $table->text('descripcion')->nullable();
            $table->json('cambios')->nullable();
            $table->timestamps();
        });

        Schema::create('reconocimiento_permisos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->boolean('ver')->default(true);
            $table->boolean('crear')->default(true);
            $table->boolean('editar')->default(true);
            $table->boolean('aprobar')->default(true);
            $table->boolean('descargar')->default(true);
            $table->boolean('cancelar')->default(true);
            $table->boolean('administrar')->default(true);
            $table->timestamps();
        });

        DB::table('reconocimiento_imagenes')->orderBy('id')->get()->each(function ($imagen) {
            DB::table('reconocimiento_imagenes')->where('id', $imagen->id)->update([
                'nombre' => $imagen->descripcion ?: 'Plantilla '.$imagen->id,
                'configuracion' => json_encode([
                    'nombre' => ['top' => 250, 'tamano' => 34],
                    'descripcion' => ['top' => 330, 'tamano' => 16],
                    'fecha' => ['top' => 470],
                    'firmas' => ['top' => 540],
                ]),
            ]);
        });

        $tipos = [
            ['nombre'=>'Aprovechamiento académico','titulo'=>'Reconocimiento','descripcion'=>'Por su destacado aprovechamiento académico, constancia y compromiso con su formación.','destinatario_tipo'=>'alumno','usa_lugar'=>false],
            ['nombre'=>'Primer lugar','titulo'=>'Reconocimiento','descripcion'=>'Por haber obtenido el primer lugar, demostrando excelencia, dedicación y disciplina.','destinatario_tipo'=>'alumno','usa_lugar'=>true],
            ['nombre'=>'Segundo lugar','titulo'=>'Reconocimiento','descripcion'=>'Por haber obtenido el segundo lugar, demostrando esfuerzo, dedicación y perseverancia.','destinatario_tipo'=>'alumno','usa_lugar'=>true],
            ['nombre'=>'Tercer lugar','titulo'=>'Reconocimiento','descripcion'=>'Por haber obtenido el tercer lugar, demostrando compromiso y perseverancia.','destinatario_tipo'=>'alumno','usa_lugar'=>true],
            ['nombre'=>'Participación destacada','titulo'=>'Reconocimiento','descripcion'=>'Por su destacada participación y valiosa contribución en las actividades realizadas.','destinatario_tipo'=>'alumno','usa_lugar'=>false],
            ['nombre'=>'Jurado calificador','titulo'=>'Reconocimiento','descripcion'=>'Por su valiosa participación como integrante del jurado calificador y su compromiso con esta actividad.','destinatario_tipo'=>'externo','usa_lugar'=>false],
            ['nombre'=>'Trayectoria docente','titulo'=>'Reconocimiento','descripcion'=>'Por su valiosa trayectoria, vocación de servicio y compromiso con la formación de las nuevas generaciones.','destinatario_tipo'=>'docente','usa_lugar'=>false],
            ['nombre'=>'Desempeño destacado','titulo'=>'Reconocimiento','descripcion'=>'Por su desempeño destacado, responsabilidad y compromiso en el cumplimiento de su labor.','destinatario_tipo'=>'docente','usa_lugar'=>false],
            ['nombre'=>'Colaboración institucional','titulo'=>'Reconocimiento','descripcion'=>'Por su valiosa colaboración y apoyo al fortalecimiento de las actividades institucionales.','destinatario_tipo'=>'externo','usa_lugar'=>false],
            ['nombre'=>'Puntualidad y asistencia','titulo'=>'Reconocimiento','descripcion'=>'Por su puntualidad, asistencia y responsabilidad demostradas durante el ciclo escolar.','destinatario_tipo'=>'alumno','usa_lugar'=>false],
            ['nombre'=>'Actividad deportiva o cultural','titulo'=>'Reconocimiento','descripcion'=>'Por su destacada participación en actividades deportivas o culturales, representando dignamente a la institución.','destinatario_tipo'=>'alumno','usa_lugar'=>false],
            ['nombre'=>'Terminación de estudios','titulo'=>'Reconocimiento','descripcion'=>'Por haber concluido satisfactoriamente sus estudios, demostrando esfuerzo, dedicación y perseverancia.','destinatario_tipo'=>'alumno','usa_lugar'=>false],
            ['nombre'=>'Nota laudatoria','titulo'=>'Nota laudatoria','descripcion'=>'Por su desempeño destacado en la labor educativa, responsabilidad, vocación de servicio y compromiso institucional.','destinatario_tipo'=>'docente','usa_lugar'=>false],
        ];
        foreach ($tipos as $tipo) {
            DB::table('reconocimiento_tipos')->insertOrIgnore($tipo + ['activo'=>true,'created_at'=>now(),'updated_at'=>now()]);
        }

        DB::table('users')->orderBy('id')->get(['id'])->each(function ($user) {
            DB::table('reconocimiento_permisos')->insertOrIgnore([
                'user_id' => $user->id,
                'ver' => true,
                'crear' => true,
                'editar' => true,
                'aprobar' => true,
                'descargar' => true,
                'cancelar' => true,
                'administrar' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reconocimiento_historiales');
        Schema::dropIfExists('reconocimiento_permisos');

        Schema::table('reconocimientos', function (Blueprint $table) {
            $table->dropForeign(['reconocimiento_imagen_id']);
            $table->foreign('reconocimiento_imagen_id')->references('id')->on('reconocimiento_imagenes')->cascadeOnDelete();
            $table->dropConstrainedForeignId('reconocimiento_evento_id');
            $table->dropConstrainedForeignId('reconocimiento_tipo_id');
            $table->dropConstrainedForeignId('credencial_id');
            $table->dropConstrainedForeignId('duplicado_de_id');
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn([
                'destinatario_tipo', 'estado', 'version', 'approved_at', 'generated_at',
                'delivered_at', 'delivery_method', 'delivery_to', 'delivery_notes',
                'cancel_reason', 'deleted_at',
            ]);
        });

        Schema::table('directivos', function (Blueprint $table) {
            $table->dropColumn(['firma', 'sello', 'vigencia_inicio', 'vigencia_fin', 'niveles', 'orden', 'activo']);
        });

        Schema::table('reconocimiento_imagenes', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'orientacion', 'configuracion', 'activo']);
        });

        Schema::dropIfExists('reconocimiento_eventos');
        Schema::dropIfExists('reconocimiento_tipos');
    }
};
