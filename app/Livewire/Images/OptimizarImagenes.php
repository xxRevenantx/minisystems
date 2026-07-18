<?php

namespace App\Livewire\Images;

use App\Services\ImagePipeline;
use Livewire\Component;

class OptimizarImagenes extends Component
{
    public string $profile = 'balanced';
    public string $format = 'webp';
    public int $quality = 82;
    public int $maxWidth = 1920;
    public int $maxHeight = 1920;
    public $targetKb = null;
    public bool $allowUpscale = false;
    public bool $preserveTransparency = true;
    public string $renamePattern = '{name}-optimizada-{index}';
    public ?string $driverName = null;
    public ?string $systemError = null;

    public function mount(): void
    {
        $this->applyProfile('balanced');

        try {
            $this->driverName = (new ImagePipeline())->driverName();
        } catch (\Throwable $exception) {
            $this->systemError = $exception->getMessage();
        }
    }

    public function updatedProfile(string $value): void
    {
        $this->applyProfile($value);
    }

    /**
     * Restaura en la interfaz los ajustes congelados de un lote recuperado.
     */
    public function cargarConfiguracionLote(array $settings): void
    {
        $allowedProfiles = array_keys($this->profiles());
        $profile = (string) ($settings['profile'] ?? 'custom');

        $this->profile = in_array($profile, $allowedProfiles, true) ? $profile : 'custom';
        $this->format = in_array(($settings['format'] ?? null), ['original', 'jpg', 'png', 'webp', 'avif'], true)
            ? (string) $settings['format']
            : 'webp';
        $this->quality = max(35, min(100, (int) ($settings['quality'] ?? 82)));
        $this->maxWidth = max(320, min(6000, (int) ($settings['max_width'] ?? 1920)));
        $this->maxHeight = max(320, min(6000, (int) ($settings['max_height'] ?? 1920)));
        $this->targetKb = filled($settings['target_kb'] ?? null)
            ? max(50, min(20000, (int) $settings['target_kb']))
            : null;
        $this->allowUpscale = (bool) ($settings['allow_upscale'] ?? false);
        $this->preserveTransparency = (bool) ($settings['preserve_transparency'] ?? true);
        $this->renamePattern = mb_substr((string) ($settings['rename_pattern'] ?? '{name}-optimizada-{index}'), 0, 100);
    }

    public function updated(string $property): void
    {
        if (in_array($property, [
            'format',
            'quality',
            'maxWidth',
            'maxHeight',
            'targetKb',
            'allowUpscale',
            'preserveTransparency',
            'renamePattern',
        ], true)) {
            $this->profile = 'custom';
        }
    }

    private function applyProfile(string $profile): void
    {
        $settings = $this->profiles()[$profile] ?? null;

        if (! $settings || $profile === 'custom') {
            return;
        }

        $this->format = $settings['format'];
        $this->quality = $settings['quality'];
        $this->maxWidth = $settings['max_width'];
        $this->maxHeight = $settings['max_height'];
        $this->targetKb = $settings['target_kb'];
        $this->preserveTransparency = $settings['preserve_transparency'];
        $this->allowUpscale = false;
    }

    /** @return array<string,array<string,mixed>> */
    private function profiles(): array
    {
        return [
            'balanced' => [
                'label' => 'Equilibrado',
                'description' => 'Buena calidad con reducción notable para uso general.',
                'format' => 'webp',
                'quality' => 82,
                'max_width' => 1920,
                'max_height' => 1920,
                'target_kb' => null,
                'preserve_transparency' => true,
            ],
            'web' => [
                'label' => 'Sitio web',
                'description' => 'Archivos ligeros para galerías, biblioteca y publicaciones.',
                'format' => 'webp',
                'quality' => 78,
                'max_width' => 1600,
                'max_height' => 1600,
                'target_kb' => null,
                'preserve_transparency' => true,
            ],
            'social' => [
                'label' => 'Redes sociales',
                'description' => 'Calidad alta y dimensiones adecuadas para publicar.',
                'format' => 'jpg',
                'quality' => 85,
                'max_width' => 2048,
                'max_height' => 2048,
                'target_kb' => null,
                'preserve_transparency' => false,
            ],
            'documents' => [
                'label' => 'Documentos e impresión',
                'description' => 'Mayor detalle para fondos, reconocimientos y documentos.',
                'format' => 'jpg',
                'quality' => 90,
                'max_width' => 3000,
                'max_height' => 3000,
                'target_kb' => null,
                'preserve_transparency' => false,
            ],
            'max' => [
                'label' => 'Máxima reducción',
                'description' => 'Reduce agresivamente para compartir o almacenar.',
                'format' => 'webp',
                'quality' => 68,
                'max_width' => 1280,
                'max_height' => 1280,
                'target_kb' => 400,
                'preserve_transparency' => true,
            ],
            'transparent' => [
                'label' => 'Logos y transparencia',
                'description' => 'Protege fondos transparentes y bordes definidos.',
                'format' => 'webp',
                'quality' => 88,
                'max_width' => 2400,
                'max_height' => 2400,
                'target_kb' => null,
                'preserve_transparency' => true,
            ],
            'custom' => [
                'label' => 'Personalizado',
                'description' => 'Control manual de formato, calidad, dimensiones y peso.',
            ],
        ];
    }

    public function render()
    {
        $profiles = $this->profiles();

        $maxFiles = max(0, (int) config('image_optimizer.max_files', 0));

        return view('livewire.images.optimizar-imagenes', [
            'profiles' => $profiles,
            'selectedProfile' => $profiles[$this->profile] ?? $profiles['custom'],
            'maxFiles' => $maxFiles,
            'hasMaxFiles' => $maxFiles > 0,
            'maxFilesLabel' => $maxFiles > 0 ? $maxFiles.' imágenes' : 'sin límite fijo',
            'maxFileMb' => round((int) config('image_optimizer.max_file_kb', 20 * 1024) / 1024),
            'uploadConcurrency' => (int) config('image_optimizer.upload_concurrency', 2),
            'zipPartMaxFiles' => (int) config('image_optimizer.zip_part_max_files', 100),
            'zipPartMaxMb' => (int) config('image_optimizer.zip_part_max_mb', 500),
            'retentionHours' => (int) config('image_optimizer.retention_hours', 24),
        ]);
    }
}
