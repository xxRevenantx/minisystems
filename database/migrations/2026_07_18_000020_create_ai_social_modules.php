<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_social_generations')) {
            Schema::create('ai_social_generations', function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('marca_id')->nullable()->constrained('marcas')->nullOnDelete();
                $table->foreignId('proyecto_creativo_id')->nullable()->constrained('proyectos_creativos')->nullOnDelete();
                $table->string('estado', 40)->default('borrador');
                $table->json('plataformas');
                $table->string('idioma', 10)->default('es');
                $table->string('tono', 60)->default('institucional');
                $table->unsignedTinyInteger('intensidad')->default(3);
                $table->string('extension', 20)->default('media');
                $table->string('nivel_emojis', 20)->default('pocos');
                $table->string('nombre_evento')->nullable();
                $table->date('fecha_evento')->nullable();
                $table->string('lugar_evento')->nullable();
                $table->string('tipo_evento')->nullable();
                $table->string('nivel_educativo')->nullable();
                $table->text('objetivo')->nullable();
                $table->text('resultados_logros')->nullable();
                $table->text('personas_autorizadas')->nullable();
                $table->longText('contexto_adicional')->nullable();
                $table->string('cta_tipo', 40)->nullable();
                $table->string('cta_personalizado')->nullable();
                $table->boolean('autorizacion_publicacion')->default(false);
                $table->boolean('contiene_menores')->default(false);
                $table->string('modelo')->nullable();
                $table->string('prompt_version', 20)->default('v1');
                $table->longText('prompt_sistema')->nullable();
                $table->longText('prompt_usuario')->nullable();
                $table->json('respuesta_estructurada')->nullable();
                $table->longText('respuesta_original')->nullable();
                $table->unsignedInteger('tokens_entrada')->nullable();
                $table->unsignedInteger('tokens_salida')->nullable();
                $table->unsignedInteger('tokens_totales')->nullable();
                $table->unsignedInteger('duracion_ms')->nullable();
                $table->string('groq_request_id')->nullable();
                $table->text('mensaje_error')->nullable();
                $table->timestamp('iniciada_at')->nullable();
                $table->timestamp('completada_at')->nullable();
                $table->timestamp('expira_at')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['user_id', 'estado']);
                $table->index(['marca_id', 'created_at']);
                $table->index(['estado', 'created_at']);
            });
        }

        if (! Schema::hasTable('ai_social_generation_images')) {
            Schema::create('ai_social_generation_images', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('ai_social_generation_id')->constrained()->cascadeOnDelete();
                $table->foreignId('archivo_multimedia_id')->nullable()->constrained('archivos_multimedia')->nullOnDelete();
                $table->string('nombre_original');
                $table->string('ruta_privada')->nullable();
                $table->string('ruta_preview')->nullable();
                $table->string('mime_type', 100)->nullable();
                $table->unsignedInteger('ancho')->nullable();
                $table->unsignedInteger('alto')->nullable();
                $table->unsignedBigInteger('peso')->nullable();
                $table->string('orientacion', 20)->nullable();
                $table->string('checksum', 64)->nullable();
                $table->unsignedSmallInteger('orden')->default(0);
                $table->boolean('seleccionada')->default(true);
                $table->boolean('portada')->default(false);
                $table->decimal('calidad_score', 5, 2)->nullable();
                $table->text('descripcion_ia')->nullable();
                $table->text('texto_alternativo')->nullable();
                $table->json('metadatos')->nullable();
                $table->timestamps();
                $table->index(['ai_social_generation_id', 'seleccionada'], 'ai_social_images_selected_idx');
            });
        }

        if (! Schema::hasTable('ai_social_versions')) {
            Schema::create('ai_social_versions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('ai_social_generation_id')->constrained()->cascadeOnDelete();
                $table->string('plataforma', 40);
                $table->string('variante', 30)->default('equilibrada');
                $table->unsignedSmallInteger('version')->default(1);
                $table->string('titulo')->nullable();
                $table->longText('copy_html')->nullable();
                $table->longText('copy_texto')->nullable();
                $table->json('hashtags')->nullable();
                $table->text('cta')->nullable();
                $table->text('texto_alt_general')->nullable();
                $table->json('textos_alt_imagenes')->nullable();
                $table->unsignedInteger('caracteres')->default(0);
                $table->boolean('favorita')->default(false);
                $table->boolean('aprobada')->default(false);
                $table->foreignId('editada_por')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['ai_social_generation_id', 'plataforma', 'variante', 'version'], 'ai_social_version_unique');
            });
        }

        if (! Schema::hasTable('marca_social_profiles')) {
            Schema::create('marca_social_profiles', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('marca_id')->unique()->constrained('marcas')->cascadeOnDelete();
                $table->string('tono_predeterminado', 60)->default('institucional');
                $table->string('nivel_emojis', 20)->default('pocos');
                $table->string('idioma', 10)->default('es');
                $table->json('hashtags_fijos')->nullable();
                $table->json('hashtags_bloqueados')->nullable();
                $table->json('palabras_preferidas')->nullable();
                $table->json('palabras_prohibidas')->nullable();
                $table->text('descripcion_voz')->nullable();
                $table->text('firma_predeterminada')->nullable();
                $table->text('instrucciones_ia')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ai_usage_logs')) {
            Schema::create('ai_usage_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('ai_social_generation_id')->nullable()->constrained()->nullOnDelete();
                $table->string('proveedor', 30)->default('groq');
                $table->string('modelo')->nullable();
                $table->string('endpoint')->nullable();
                $table->string('estado', 40);
                $table->unsignedInteger('tokens_entrada')->nullable();
                $table->unsignedInteger('tokens_salida')->nullable();
                $table->unsignedInteger('tokens_totales')->nullable();
                $table->unsignedInteger('duracion_ms')->nullable();
                $table->unsignedInteger('http_status')->nullable();
                $table->string('request_id')->nullable();
                $table->unsignedInteger('rate_limit_remaining')->nullable();
                $table->timestamp('rate_limit_reset_at')->nullable();
                $table->text('error')->nullable();
                $table->json('metadatos')->nullable();
                $table->timestamps();
                $table->index(['proveedor', 'created_at']);
                $table->index(['user_id', 'created_at']);
            });
        }

        if (! Schema::hasTable('publicacion_social_archivos')) {
            Schema::create('publicacion_social_archivos', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('publicacion_social_id')->constrained('publicaciones_sociales')->cascadeOnDelete();
                $table->foreignId('archivo_multimedia_id')->constrained('archivos_multimedia')->cascadeOnDelete();
                $table->unsignedSmallInteger('orden')->default(0);
                $table->boolean('portada')->default(false);
                $table->text('texto_alternativo')->nullable();
                $table->timestamps();
                $table->unique(['publicacion_social_id', 'archivo_multimedia_id'], 'publicacion_archivo_unique');
            });
        }

        Schema::table('publicaciones_sociales', function (Blueprint $table): void {
            if (! Schema::hasColumn('publicaciones_sociales', 'ai_social_generation_id')) {
                $table->foreignId('ai_social_generation_id')->nullable()->after('archivo_multimedia_id')->constrained('ai_social_generations')->nullOnDelete();
            }
            if (! Schema::hasColumn('publicaciones_sociales', 'grupo_publicacion_uuid')) {
                $table->uuid('grupo_publicacion_uuid')->nullable()->after('ai_social_generation_id')->index();
            }
            if (! Schema::hasColumn('publicaciones_sociales', 'variante_ia')) {
                $table->string('variante_ia', 30)->nullable()->after('red_social');
            }
            if (! Schema::hasColumn('publicaciones_sociales', 'generada_por_ia')) {
                $table->boolean('generada_por_ia')->default(false)->after('variante_ia');
            }
            if (! Schema::hasColumn('publicaciones_sociales', 'copy_html')) {
                $table->longText('copy_html')->nullable()->after('copy');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('publicaciones_sociales')) {
            Schema::table('publicaciones_sociales', function (Blueprint $table): void {
                foreach (['ai_social_generation_id', 'grupo_publicacion_uuid', 'variante_ia', 'generada_por_ia', 'copy_html'] as $column) {
                    if (Schema::hasColumn('publicaciones_sociales', $column)) {
                        if ($column === 'ai_social_generation_id') {
                            $table->dropForeign([$column]);
                        }
                        $table->dropColumn($column);
                    }
                }
            });
        }

        Schema::dropIfExists('publicacion_social_archivos');
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('marca_social_profiles');
        Schema::dropIfExists('ai_social_versions');
        Schema::dropIfExists('ai_social_generation_images');
        Schema::dropIfExists('ai_social_generations');
    }
};
