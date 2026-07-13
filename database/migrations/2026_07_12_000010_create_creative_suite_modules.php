<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marcas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->string('tipo')->default('cliente');
            $table->string('contacto')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 40)->nullable();
            $table->string('sitio_web')->nullable();
            $table->string('logo')->nullable();
            $table->string('logo_secundario')->nullable();
            $table->string('color_primario', 20)->default('#006492');
            $table->string('color_secundario', 20)->default('#88AC2E');
            $table->json('tipografias')->nullable();
            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['activo', 'tipo']);
        });

        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('marca_id')->nullable()->constrained('marcas')->nullOnDelete();
            $table->string('tipo')->default('contacto');
            $table->string('nombre');
            $table->string('foto')->nullable();
            $table->string('cargo')->nullable();
            $table->string('organizacion')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 40)->nullable();
            $table->string('identificador')->nullable();
            $table->json('tags')->nullable();
            $table->json('datos_extra')->nullable();
            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['marca_id', 'activo']);
            $table->index(['tipo', 'nombre']);
        });

        Schema::create('proyectos_creativos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('marca_id')->nullable()->constrained('marcas')->nullOnDelete();
            $table->string('nombre');
            $table->string('tipo')->default('campaña');
            $table->string('estado')->default('borrador');
            $table->string('prioridad')->default('media');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_entrega')->nullable();
            $table->text('descripcion')->nullable();
            $table->json('tags')->nullable();
            $table->json('configuracion')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['marca_id', 'estado']);
            $table->index(['fecha_entrega', 'prioridad']);
        });

        Schema::create('persona_proyecto_creativo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained('personas')->cascadeOnDelete();
            $table->foreignId('proyecto_creativo_id')->constrained('proyectos_creativos')->cascadeOnDelete();
            $table->string('rol')->nullable();
            $table->timestamps();
            $table->unique(['persona_id', 'proyecto_creativo_id'], 'persona_proyecto_unico');
        });

        Schema::create('archivos_multimedia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('marca_id')->nullable()->constrained('marcas')->nullOnDelete();
            $table->foreignId('proyecto_creativo_id')->nullable()->constrained('proyectos_creativos')->nullOnDelete();
            $table->string('nombre');
            $table->string('categoria')->default('imagen');
            $table->string('archivo');
            $table->string('disk')->default('public');
            $table->string('mime', 120)->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedInteger('ancho')->nullable();
            $table->unsignedInteger('alto')->nullable();
            $table->unsignedBigInteger('peso')->nullable();
            $table->string('orientacion', 20)->nullable();
            $table->boolean('transparencia')->nullable();
            $table->json('tags')->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['categoria', 'activo']);
            $table->index(['marca_id', 'proyecto_creativo_id']);
        });

        Schema::create('presets_sociales', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('red_social')->default('General');
            $table->unsignedInteger('ancho');
            $table->unsignedInteger('alto');
            $table->string('orientacion', 20);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['nombre', 'ancho', 'alto'], 'preset_nombre_medidas_unico');
        });

        Schema::create('plantillas_creativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('marca_id')->nullable()->constrained('marcas')->nullOnDelete();
            $table->foreignId('fondo_archivo_id')->nullable()->constrained('archivos_multimedia')->nullOnDelete();
            $table->string('nombre');
            $table->string('tipo')->default('general');
            $table->unsignedInteger('ancho')->default(1920);
            $table->unsignedInteger('alto')->default(1080);
            $table->string('orientacion', 20)->default('horizontal');
            $table->json('estructura')->nullable();
            $table->json('configuracion_impresion')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('estado')->default('borrador');
            $table->unsignedInteger('version')->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tipo', 'estado']);
            $table->index(['marca_id', 'activo']);
        });

        Schema::create('plantilla_versiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plantilla_creativa_id')->constrained('plantillas_creativas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('version');
            $table->json('estructura')->nullable();
            $table->json('configuracion')->nullable();
            $table->string('nota')->nullable();
            $table->timestamps();
            $table->unique(['plantilla_creativa_id', 'version'], 'plantilla_version_unica');
        });

        Schema::create('solicitudes_creativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('marca_id')->nullable()->constrained('marcas')->nullOnDelete();
            $table->foreignId('proyecto_creativo_id')->nullable()->constrained('proyectos_creativos')->nullOnDelete();
            $table->string('titulo');
            $table->string('tipo')->default('diseño');
            $table->string('estado')->default('pendiente');
            $table->string('prioridad')->default('media');
            $table->string('solicitante')->nullable();
            $table->string('contacto')->nullable();
            $table->dateTime('fecha_entrega')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['estado', 'prioridad']);
            $table->index(['fecha_entrega']);
        });

        Schema::create('publicaciones_sociales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('marca_id')->nullable()->constrained('marcas')->nullOnDelete();
            $table->foreignId('proyecto_creativo_id')->nullable()->constrained('proyectos_creativos')->nullOnDelete();
            $table->foreignId('archivo_multimedia_id')->nullable()->constrained('archivos_multimedia')->nullOnDelete();
            $table->string('titulo');
            $table->string('red_social');
            $table->string('estado')->default('borrador');
            $table->dateTime('programada_at')->nullable();
            $table->dateTime('publicada_at')->nullable();
            $table->text('copy')->nullable();
            $table->text('hashtags')->nullable();
            $table->string('url_publicacion')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['red_social', 'estado']);
            $table->index(['programada_at']);
        });

        Schema::create('historial_exportaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('marca_id')->nullable()->constrained('marcas')->nullOnDelete();
            $table->foreignId('proyecto_creativo_id')->nullable()->constrained('proyectos_creativos')->nullOnDelete();
            $table->foreignId('plantilla_creativa_id')->nullable()->constrained('plantillas_creativas')->nullOnDelete();
            $table->string('tipo');
            $table->string('formato', 20)->nullable();
            $table->string('archivo')->nullable();
            $table->unsignedInteger('cantidad')->default(1);
            $table->json('configuracion')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->index(['tipo', 'created_at']);
        });

        Schema::create('registros_validacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('persona_id')->nullable()->constrained('personas')->nullOnDelete();
            $table->foreignId('proyecto_creativo_id')->nullable()->constrained('proyectos_creativos')->nullOnDelete();
            $table->string('codigo')->unique();
            $table->string('tipo')->default('documento');
            $table->string('titulo');
            $table->string('estado')->default('valido');
            $table->date('emitido_at')->nullable();
            $table->date('vence_at')->nullable();
            $table->json('datos_publicos')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->index(['estado', 'tipo']);
        });

        Schema::create('actividad_creativa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('modulo');
            $table->string('accion');
            $table->string('entidad_tipo')->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->string('descripcion')->nullable();
            $table->json('datos')->nullable();
            $table->timestamps();
            $table->index(['modulo', 'created_at']);
            $table->index(['entidad_tipo', 'entidad_id']);
        });

        if (Schema::hasTable('reconocimientos')) {
            Schema::table('reconocimientos', function (Blueprint $table) {
                if (! Schema::hasColumn('reconocimientos', 'marca_id')) {
                    $table->foreignId('marca_id')->nullable()->after('id')->constrained('marcas')->nullOnDelete();
                }
                if (! Schema::hasColumn('reconocimientos', 'proyecto_creativo_id')) {
                    $table->foreignId('proyecto_creativo_id')->nullable()->after('marca_id')->constrained('proyectos_creativos')->nullOnDelete();
                }
                if (! Schema::hasColumn('reconocimientos', 'persona_id')) {
                    $table->foreignId('persona_id')->nullable()->after('proyecto_creativo_id')->constrained('personas')->nullOnDelete();
                }
                if (! Schema::hasColumn('reconocimientos', 'registro_validacion_id')) {
                    $table->foreignId('registro_validacion_id')->nullable()->after('persona_id')->constrained('registros_validacion')->nullOnDelete();
                }
            });
        }

        if (Schema::hasTable('credenciales')) {
            Schema::table('credenciales', function (Blueprint $table) {
                if (! Schema::hasColumn('credenciales', 'marca_id')) {
                    $table->foreignId('marca_id')->nullable()->after('id')->constrained('marcas')->nullOnDelete();
                }
                if (! Schema::hasColumn('credenciales', 'proyecto_creativo_id')) {
                    $table->foreignId('proyecto_creativo_id')->nullable()->after('marca_id')->constrained('proyectos_creativos')->nullOnDelete();
                }
                if (! Schema::hasColumn('credenciales', 'persona_id')) {
                    $table->foreignId('persona_id')->nullable()->after('proyecto_creativo_id')->constrained('personas')->nullOnDelete();
                }
                if (! Schema::hasColumn('credenciales', 'registro_validacion_id')) {
                    $table->foreignId('registro_validacion_id')->nullable()->after('persona_id')->constrained('registros_validacion')->nullOnDelete();
                }
                if (! Schema::hasColumn('credenciales', 'tipo')) {
                    $table->string('tipo')->default('general')->after('registro_validacion_id');
                }
                if (! Schema::hasColumn('credenciales', 'folio')) {
                    $table->string('folio')->nullable()->after('tipo');
                }
                if (! Schema::hasColumn('credenciales', 'cargo')) {
                    $table->string('cargo')->nullable()->after('nombre');
                }
                if (! Schema::hasColumn('credenciales', 'organizacion')) {
                    $table->string('organizacion')->nullable()->after('cargo');
                }
                if (! Schema::hasColumn('credenciales', 'correo')) {
                    $table->string('correo')->nullable()->after('telefono');
                }
                if (! Schema::hasColumn('credenciales', 'foto')) {
                    $table->string('foto')->nullable()->after('correo');
                }
                if (! Schema::hasColumn('credenciales', 'estado')) {
                    $table->string('estado')->default('activa')->after('foto');
                }
                if (! Schema::hasColumn('credenciales', 'datos_extra')) {
                    $table->json('datos_extra')->nullable()->after('estado');
                }
                if (! Schema::hasColumn('credenciales', 'tiene_reverso')) {
                    $table->boolean('tiene_reverso')->default(false)->after('datos_extra');
                }
                if (! Schema::hasColumn('credenciales', 'reverso_texto')) {
                    $table->text('reverso_texto')->nullable()->after('tiene_reverso');
                }
                if (! Schema::hasColumn('credenciales', 'reverso_imagen')) {
                    $table->string('reverso_imagen')->nullable()->after('reverso_texto');
                }
            });

            Schema::table('credenciales', function (Blueprint $table) {
                $table->string('matricula')->nullable()->change();
                $table->string('curp')->nullable()->change();
                $table->string('nivel')->nullable()->change();
            });
        }

        if (Schema::hasTable('reconocimiento_tipos')) {
            $tiposGenerales = [
                ['Participación en evento', 'Por su valiosa participación y aportación durante el evento.'],
                ['Ponente o conferencista', 'Por compartir sus conocimientos y experiencia como ponente.'],
                ['Colaboración destacada', 'Por su compromiso, colaboración y contribución al logro de los objetivos.'],
                ['Trayectoria profesional', 'Por su destacada trayectoria, liderazgo y aportaciones en su ámbito profesional.'],
                ['Asistencia a curso o taller', 'Por haber participado satisfactoriamente en el curso o taller.'],
                ['Voluntariado', 'Por su dedicación, solidaridad y valiosa participación como voluntario.'],
                ['Patrocinio', 'Por su invaluable apoyo y contribución como patrocinador.'],
                ['Organización de evento', 'Por su responsabilidad y destacada labor en la organización del evento.'],
            ];

            foreach ($tiposGenerales as [$nombre, $descripcion]) {
                DB::table('reconocimiento_tipos')->updateOrInsert(
                    ['nombre' => $nombre],
                    [
                        'titulo' => 'Reconocimiento',
                        'descripcion' => $descripcion,
                        'destinatario_tipo' => 'persona',
                        'usa_lugar' => false,
                        'activo' => true,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }

        $now = now();
        $presets = [
            ['Instagram Post', 'Instagram', 1080, 1080, 'cuadrada', 'Publicación cuadrada estándar.'],
            ['Instagram Retrato', 'Instagram', 1080, 1350, 'vertical', 'Publicación vertical para mayor presencia en el feed.'],
            ['Instagram Story', 'Instagram', 1080, 1920, 'vertical', 'Historia y portada de Reel.'],
            ['Facebook Post', 'Facebook', 1200, 630, 'horizontal', 'Publicación horizontal recomendada.'],
            ['Facebook Portada', 'Facebook', 1640, 624, 'horizontal', 'Portada para página.'],
            ['WhatsApp Estado', 'WhatsApp', 1080, 1920, 'vertical', 'Estado vertical de pantalla completa.'],
            ['LinkedIn Post', 'LinkedIn', 1200, 1200, 'cuadrada', 'Publicación cuadrada profesional.'],
            ['YouTube Miniatura', 'YouTube', 1280, 720, 'horizontal', 'Miniatura 16:9.'],
            ['TikTok Portada', 'TikTok', 1080, 1920, 'vertical', 'Portada vertical.'],
            ['X Post', 'X', 1600, 900, 'horizontal', 'Publicación horizontal 16:9.'],
        ];

        foreach ($presets as [$nombre, $red, $ancho, $alto, $orientacion, $descripcion]) {
            DB::table('presets_sociales')->insertOrIgnore([
                'nombre' => $nombre,
                'red_social' => $red,
                'ancho' => $ancho,
                'alto' => $alto,
                'orientacion' => $orientacion,
                'descripcion' => $descripcion,
                'activo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('credenciales')) {
            $foreignColumns = ['registro_validacion_id', 'persona_id', 'proyecto_creativo_id', 'marca_id'];
            foreach ($foreignColumns as $column) {
                if (Schema::hasColumn('credenciales', $column)) {
                    Schema::table('credenciales', fn (Blueprint $table) => $table->dropForeign([$column]));
                    Schema::table('credenciales', fn (Blueprint $table) => $table->dropColumn($column));
                }
            }

            $extraColumns = ['tipo', 'folio', 'cargo', 'organizacion', 'correo', 'foto', 'estado', 'datos_extra', 'tiene_reverso', 'reverso_texto', 'reverso_imagen'];
            foreach ($extraColumns as $column) {
                if (Schema::hasColumn('credenciales', $column)) {
                    Schema::table('credenciales', fn (Blueprint $table) => $table->dropColumn($column));
                }
            }
        }

        if (Schema::hasTable('reconocimientos')) {
            foreach (['registro_validacion_id', 'persona_id', 'proyecto_creativo_id', 'marca_id'] as $column) {
                if (Schema::hasColumn('reconocimientos', $column)) {
                    Schema::table('reconocimientos', fn (Blueprint $table) => $table->dropForeign([$column]));
                    Schema::table('reconocimientos', fn (Blueprint $table) => $table->dropColumn($column));
                }
            }
        }

        Schema::dropIfExists('actividad_creativa');
        Schema::dropIfExists('registros_validacion');
        Schema::dropIfExists('historial_exportaciones');
        Schema::dropIfExists('publicaciones_sociales');
        Schema::dropIfExists('solicitudes_creativas');
        Schema::dropIfExists('plantilla_versiones');
        Schema::dropIfExists('plantillas_creativas');
        Schema::dropIfExists('presets_sociales');
        Schema::dropIfExists('archivos_multimedia');
        Schema::dropIfExists('persona_proyecto_creativo');
        Schema::dropIfExists('proyectos_creativos');
        Schema::dropIfExists('personas');
        Schema::dropIfExists('marcas');
    }
};
