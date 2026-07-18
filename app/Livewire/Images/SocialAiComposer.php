<?php

namespace App\Livewire\Images;

use App\Jobs\GenerateSocialCopyWithGroq;
use App\Models\AiSocialGeneration;
use App\Models\AiSocialVersion;
use App\Models\AiUsageLog;
use App\Models\ArchivoMultimedia;
use App\Models\Marca;
use App\Models\ProyectoCreativo;
use App\Models\PublicacionSocial;
use App\Services\CreativeActivity;
use App\Services\Groq\GroqClient;
use App\Services\Groq\SocialCopyResponseParser;
use App\Services\Images\AiImagePreviewService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Livewire\Component;
use Livewire\WithFileUploads;

class SocialAiComposer extends Component
{
    use WithFileUploads;

    /** @var array<\Livewire\Features\FileUploads\TemporaryUploadedFile> */
    public array $images = [];
    public ?int $generationId = null;
    public ?int $marcaId = null;
    public ?int $proyectoId = null;
    public array $platforms = ['facebook', 'instagram'];
    public string $eventName = '';
    public string $eventDate = '';
    public string $eventPlace = '';
    public string $eventType = '';
    public string $educationLevel = '';
    public string $objective = '';
    public string $achievements = '';
    public string $authorizedPeople = '';
    public string $additionalContext = '';
    public string $tone = 'institucional';
    public int $toneIntensity = 3;
    public string $length = 'media';
    public string $emojiLevel = 'pocos';
    public string $language = 'es';
    public string $ctaType = 'automatico';
    public string $customCta = '';
    public bool $containsMinors = false;
    public bool $publicationAuthorized = false;
    public ?int $selectedVersionId = null;
    public string $editingTitle = '';
    public string $editingCopy = '';
    public string $editingHashtags = '';
    public string $editingCta = '';
    public array $selectedVersions = [];

    protected function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1', 'max:'.config('groq.max_images', 10)],
            'images.*' => ['required', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(20 * 1024)],
            'marcaId' => ['nullable', 'integer', 'exists:marcas,id'],
            'proyectoId' => ['nullable', 'integer', 'exists:proyectos_creativos,id'],
            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*' => ['in:facebook,instagram,whatsapp,tiktok,x'],
            'eventName' => ['nullable', 'string', 'max:255'],
            'eventDate' => ['nullable', 'date'],
            'eventPlace' => ['nullable', 'string', 'max:255'],
            'eventType' => ['nullable', 'string', 'max:255'],
            'educationLevel' => ['nullable', 'string', 'max:255'],
            'objective' => ['required', 'string', 'min:10', 'max:3000'],
            'achievements' => ['nullable', 'string', 'max:3000'],
            'authorizedPeople' => ['nullable', 'string', 'max:2000'],
            'additionalContext' => ['nullable', 'string', 'max:5000'],
            'tone' => ['required', 'in:institucional,formal,emotivo,tranquilo,alegre,inspirador,juvenil,promocional,agradecimiento,informativo,personalizado'],
            'toneIntensity' => ['required', 'integer', 'between:1,5'],
            'length' => ['required', 'in:corta,media,larga'],
            'emojiLevel' => ['required', 'in:ninguno,pocos,moderados,muchos'],
            'language' => ['required', 'in:es,en'],
            'ctaType' => ['required', 'in:automatico,conoce_mas,inscribete,comparte,felicita,contactanos,personalizado,ninguno'],
            'customCta' => ['nullable', 'string', 'max:255'],
            'containsMinors' => ['boolean'],
            'publicationAuthorized' => ['boolean'],
        ];
    }

    public function updatedMarcaId($value): void
    {
        $marca = $value ? Marca::with('perfilSocial')->find($value) : null;
        if (! $marca?->perfilSocial) {
            return;
        }

        $this->tone = $marca->perfilSocial->tono_predeterminado ?: $this->tone;
        $this->emojiLevel = $marca->perfilSocial->nivel_emojis ?: $this->emojiLevel;
        $this->language = $marca->perfilSocial->idioma ?: $this->language;
    }

    public function generar(AiImagePreviewService $previewService, GroqClient $groq): void
    {
        if (! $groq->configured()) {
            $this->addError('groq', 'Groq no está configurado. Agrega GROQ_API_KEY en el archivo .env.');
            return;
        }

        $this->validate();

        $todayCount = AiUsageLog::query()
            ->where('user_id', auth()->id())
            ->whereDate('created_at', today())
            ->where('estado', 'completada')
            ->count();

        if ($todayCount >= (int) config('groq.daily_limit_per_user', 30)) {
            $this->addError('groq', 'Alcanzaste el límite diario de generaciones con IA.');
            return;
        }

        $generation = DB::transaction(function () use ($previewService): AiSocialGeneration {
            $generation = AiSocialGeneration::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => auth()->id(),
                'marca_id' => $this->marcaId,
                'proyecto_creativo_id' => $this->proyectoId,
                'estado' => 'preparando_imagenes',
                'plataformas' => array_values(array_unique($this->platforms)),
                'idioma' => $this->language,
                'tono' => $this->tone,
                'intensidad' => $this->toneIntensity,
                'extension' => $this->length,
                'nivel_emojis' => $this->emojiLevel,
                'nombre_evento' => $this->eventName ?: null,
                'fecha_evento' => $this->eventDate ?: null,
                'lugar_evento' => $this->eventPlace ?: null,
                'tipo_evento' => $this->eventType ?: null,
                'nivel_educativo' => $this->educationLevel ?: null,
                'objetivo' => $this->objective,
                'resultados_logros' => $this->achievements ?: null,
                'personas_autorizadas' => $this->authorizedPeople ?: null,
                'contexto_adicional' => $this->additionalContext ?: null,
                'cta_tipo' => $this->ctaType,
                'cta_personalizado' => $this->customCta ?: null,
                'autorizacion_publicacion' => $this->publicationAuthorized,
                'contiene_menores' => $this->containsMinors,
                'modelo' => config('groq.vision_model'),
                'expira_at' => now()->addHours((int) config('groq.temporary_retention_hours', 24)),
            ]);

            foreach ($this->images as $index => $file) {
                $prepared = $previewService->prepare($file, $generation->uuid, $index + 1);
                $generation->imagenes()->create(array_merge($prepared, [
                    'nombre_original' => $file->getClientOriginalName(),
                    'orden' => $index + 1,
                    'seleccionada' => true,
                    'portada' => $index === 0,
                    'calidad_score' => $this->qualityScore($prepared),
                ]));
            }

            $generation->update(['estado' => 'en_cola']);
            return $generation;
        });

        GenerateSocialCopyWithGroq::dispatch($generation->id)->afterCommit();
        CreativeActivity::log('system_images_ai', 'generar', $generation, 'Redacción IA en cola', ['plataformas' => $generation->plataformas]);
        $this->generationId = $generation->id;
        $this->images = [];
        $this->resetValidation();
        $this->dispatch('swal', ['title' => 'Las fotografías se enviaron a la cola de Groq', 'icon' => 'success', 'position' => 'top-end']);
    }

    public function refrescar(): void
    {
        if (! $this->generationId) {
            return;
        }

        $generation = $this->generation;
        if ($generation?->estado === 'completada' && ! $this->selectedVersionId) {
            foreach ($generation->versiones->groupBy('plataforma') as $platform => $versions) {
                $preferred = $versions->firstWhere('variante', 'equilibrada') ?: $versions->first();
                if ($preferred) {
                    $this->selectedVersions[$platform] = $preferred->id;
                }
            }
            $this->seleccionarVersion((int) ($generation->versiones->first()?->id ?? 0));
        }
    }

    public function seleccionarVersion(int $id): void
    {
        $version = AiSocialVersion::whereKey($id)
            ->whereHas('generacion', fn ($query) => $query->where('user_id', auth()->id()))
            ->firstOrFail();

        $this->selectedVersionId = $version->id;
        $this->editingTitle = (string) $version->titulo;
        $this->editingCopy = (string) $version->copy_html;
        $this->editingHashtags = implode(' ', $version->hashtags ?? []);
        $this->editingCta = (string) $version->cta;
        $this->selectedVersions[$version->plataforma] = $version->id;
        $this->dispatch('social-copy-editor-updated', html: $this->editingCopy);
    }

    public function guardarEdicion(SocialCopyResponseParser $parser): void
    {
        if (! $this->selectedVersionId) {
            return;
        }

        $version = AiSocialVersion::whereKey($this->selectedVersionId)
            ->whereHas('generacion', fn ($query) => $query->where('user_id', auth()->id()))
            ->firstOrFail();
        $safe = strip_tags($this->editingCopy, '<p><br><strong><em><u><ul><ol><li><a>');
        $plain = $parser->plainText($safe);
        $hashtags = collect(preg_split('/\s+/u', trim($this->editingHashtags)) ?: [])
            ->filter()->map(fn ($tag) => str_starts_with($tag, '#') ? $tag : '#'.ltrim($tag, '#'))->unique()->values()->all();

        $version->update([
            'titulo' => trim($this->editingTitle) ?: null,
            'copy_html' => $safe,
            'copy_texto' => $plain,
            'hashtags' => $hashtags,
            'cta' => trim($this->editingCta) ?: null,
            'caracteres' => mb_strlen($plain),
            'editada_por' => auth()->id(),
        ]);
        $this->dispatch('swal', ['title' => 'Versión actualizada', 'icon' => 'success', 'position' => 'top-end']);
    }

    public function crearBorradoresPublicaciones(): void
    {
        $generation = $this->generation;
        abort_unless($generation && $generation->user_id === auth()->id(), 403);

        if ($generation->contiene_menores && ! $generation->autorizacion_publicacion) {
            $this->addError('authorization', 'Confirma la autorización de difusión antes de enviar el contenido a Publicaciones.');
            return;
        }

        $selectedIds = array_values(array_filter(array_map('intval', $this->selectedVersions)));
        $versions = AiSocialVersion::where('ai_social_generation_id', $generation->id)->whereIn('id', $selectedIds)->get();
        if ($versions->isEmpty()) {
            $this->addError('versions', 'Selecciona al menos una versión para enviar a Publicaciones.');
            return;
        }

        DB::transaction(function () use ($generation, $versions): void {
            $groupUuid = (string) Str::uuid();
            $library = [];

            foreach ($generation->imagenes as $image) {
                $target = 'creative/library/social/'.now()->format('Y/m').'/'.$generation->uuid.'-'.basename((string) $image->ruta_privada);
                Storage::disk('public')->put($target, Storage::disk('local')->get((string) $image->ruta_privada));
                $library[$image->id] = ArchivoMultimedia::create([
                    'user_id' => auth()->id(), 'marca_id' => $generation->marca_id,
                    'proyecto_creativo_id' => $generation->proyecto_creativo_id,
                    'nombre' => $image->nombre_original, 'categoria' => 'fotografia', 'archivo' => $target,
                    'disk' => 'public', 'mime' => $image->mime_type, 'extension' => 'jpg', 'ancho' => $image->ancho,
                    'alto' => $image->alto, 'peso' => $image->peso, 'orientacion' => $image->orientacion,
                    'descripcion' => $image->descripcion_ia, 'activo' => true,
                ]);
                $image->update(['archivo_multimedia_id' => $library[$image->id]->id]);
            }

            foreach ($versions as $version) {
                $cover = $generation->imagenes->firstWhere('portada', true) ?: $generation->imagenes->first();
                $publication = PublicacionSocial::create([
                    'user_id' => auth()->id(), 'marca_id' => $generation->marca_id,
                    'proyecto_creativo_id' => $generation->proyecto_creativo_id,
                    'archivo_multimedia_id' => $cover ? ($library[$cover->id]->id ?? null) : null,
                    'ai_social_generation_id' => $generation->id, 'grupo_publicacion_uuid' => $groupUuid,
                    'titulo' => $version->titulo ?: ($generation->nombre_evento ?: 'Publicación generada con IA'),
                    'red_social' => ucfirst($version->plataforma), 'variante_ia' => $version->variante,
                    'generada_por_ia' => true, 'estado' => 'borrador', 'copy' => $version->copy_texto,
                    'copy_html' => $version->copy_html, 'hashtags' => implode(' ', $version->hashtags ?? []),
                    'notas' => $version->cta ? 'CTA: '.$version->cta : null,
                ]);

                foreach ($generation->imagenes as $image) {
                    $publication->archivos()->attach($library[$image->id]->id, [
                        'orden' => $image->orden, 'portada' => $image->portada,
                        'texto_alternativo' => $image->texto_alternativo,
                    ]);
                }
                $version->update(['aprobada' => true]);
            }

            $generation->update(['estado' => 'enviada_publicaciones', 'autorizacion_publicacion' => $this->publicationAuthorized]);
        });

        CreativeActivity::log('system_images_ai', 'enviar_publicaciones', $generation, 'Borradores creados en Publicaciones');
        $this->dispatch('swal', ['title' => 'Borradores enviados a Publicaciones', 'icon' => 'success', 'position' => 'top-end']);
    }

    public function nuevaGeneracion(): void
    {
        $this->reset(['images', 'generationId', 'selectedVersionId', 'editingTitle', 'editingCopy', 'editingHashtags', 'editingCta', 'selectedVersions']);
        $this->resetValidation();
    }

    public function getGenerationProperty(): ?AiSocialGeneration
    {
        return $this->generationId
            ? AiSocialGeneration::with(['imagenes', 'versiones', 'marca', 'proyecto'])->where('user_id', auth()->id())->find($this->generationId)
            : null;
    }

    private function qualityScore(array $prepared): float
    {
        $pixels = ((int) $prepared['ancho']) * ((int) $prepared['alto']);
        return round(min(100, max(10, ($pixels / 2_560_000) * 100)), 2);
    }

    public function render()
    {
        return view('livewire.images.social-ai-composer', [
            'generation' => $this->generation,
            'marcas' => Marca::query()->where('activo', true)->orderBy('nombre')->get(),
            'proyectos' => ProyectoCreativo::query()->when($this->marcaId, fn ($query) => $query->where('marca_id', $this->marcaId))->orderBy('nombre')->get(),
        ]);
    }
}
