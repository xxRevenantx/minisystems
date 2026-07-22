<?php

namespace App\Services\Pdf;

use App\Models\PdfBatch;
use App\Models\PdfItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PdfProcessor
{
    public function __construct(private readonly PdfToolchain $tools)
    {
    }

    /** @return array{page_count:int,encrypted:bool,thumbnails:array<int,string>} */
    public function inspect(PdfItem $item): array
    {
        if (! $item->source_path) {
            throw new RuntimeException('No se encontró el archivo original.');
        }

        if ($this->isImage($item)) {
            return [
                'page_count' => 1,
                'encrypted' => false,
                'thumbnails' => [],
            ];
        }

        $this->tools->ensure(['qpdf', 'pdfinfo', 'pdftoppm']);
        $source = $this->absolute($item->source_path);
        $password = (string) ($item->secret ?? '');
        $encrypted = $this->isEncrypted($source);
        $pageCount = $this->pageCount($source, $password);
        $thumbs = $this->generateThumbnails($item, $source, $password, $pageCount);

        return [
            'page_count' => $pageCount,
            'encrypted' => $encrypted,
            'thumbnails' => $thumbs,
        ];
    }

    /** @return array{output_name:string,output_path:string,output_size:int,warnings:array<int,string>} */
    public function compress(PdfItem $item, array $settings): array
    {
        $this->tools->ensure(['qpdf', 'ghostscript']);
        $disk = Storage::disk('local');
        $source = $this->decryptedInput($item);
        $base = $this->safeBase($item->original_name);
        $outputName = $base.'_comprimido.pdf';
        $outputPath = $item->batch->basePath().'/outputs/'.$outputName;
        $output = $this->absolute($outputPath);
        $this->ensureDirectory(dirname($output));
        @unlink($output);
        $profile = (string) ($settings['compression_profile'] ?? 'auto');

        if ($profile === 'low') {
            $this->tools->execute('qpdf', [
                $source,
                '--object-streams=generate',
                '--stream-data=compress',
                '--recompress-flate',
                '--compression-level=9',
                '--linearize',
                $output,
            ]);
        } else {
            $preset = match ($profile) {
                'high' => '/screen',
                'medium', 'auto' => '/ebook',
                default => null,
            };

            $arguments = [
                '-dSAFER',
                '-dBATCH',
                '-dNOPAUSE',
                '-sDEVICE=pdfwrite',
                '-dCompatibilityLevel=1.7',
                '-dDetectDuplicateImages=true',
                '-dCompressFonts=true',
                '-dSubsetFonts=true',
                '-sOutputFile='.$output,
            ];

            if ($preset) {
                $arguments[] = '-dPDFSETTINGS='.$preset;
            } else {
                $quality = max(35, min(100, (int) ($settings['custom_quality'] ?? 75)));
                $dpi = (int) round(72 + (($quality - 35) / 65) * 228);
                $arguments = array_merge($arguments, [
                    '-dDownsampleColorImages=true',
                    '-dColorImageDownsampleType=/Bicubic',
                    '-dColorImageResolution='.$dpi,
                    '-dDownsampleGrayImages=true',
                    '-dGrayImageDownsampleType=/Bicubic',
                    '-dGrayImageResolution='.$dpi,
                    '-dDownsampleMonoImages=true',
                    '-dMonoImageResolution='.max(150, $dpi * 2),
                    '-dJPEGQ='.$quality,
                ]);
            }

            $arguments[] = $source;
            $this->tools->execute('ghostscript', $arguments);
        }

        $warnings = $profile === 'low'
            ? []
            : ['Los perfiles automático, medio, alto y personalizado pueden simplificar formularios o elementos interactivos complejos. Usa el perfil bajo cuando la fidelidad estructural sea prioritaria.'];
        $originalSize = (int) $item->original_size;
        $newSize = is_file($output) ? (int) filesize($output) : 0;

        if ($newSize <= 0) {
            throw new RuntimeException('La herramienta no generó un PDF comprimido válido.');
        }

        if ($newSize >= $originalSize) {
            copy($source, $output);
            $newSize = (int) filesize($output);
            $warnings[] = 'La compresión no redujo el tamaño. Se conservó el archivo original para evitar que aumentara de peso.';
        }

        $this->cleanupPreparedInput($item, $source);

        return [
            'output_name' => $outputName,
            'output_path' => $outputPath,
            'output_size' => $newSize,
            'warnings' => $warnings,
        ];
    }

    /** @return array{output_name:string,output_path:string,output_size:int} */
    public function combine(PdfBatch $batch, array $settings): array
    {
        $this->tools->ensure(['qpdf']);
        $batch->loadMissing('items');
        $prepared = [];
        $cleanup = [];

        foreach ($batch->items->sortBy('position') as $item) {
            if (! $item->source_path) {
                throw new RuntimeException("Falta el archivo {$item->original_name}.");
            }

            if ($this->isImage($item)) {
                $path = $this->imageToPdf($item);
                $prepared[] = $path;
                $cleanup[] = $path;
            } else {
                $path = $this->decryptedInput($item);
                $prepared[] = $path;
                if ($path !== $this->absolute($item->source_path)) {
                    $cleanup[] = $path;
                }
            }
        }

        $requested = trim((string) ($settings['output_name'] ?? 'documentos_combinados'));
        $outputName = $this->safeBase($requested !== '' ? $requested : 'documentos_combinados').'.pdf';
        $outputPath = $batch->basePath().'/outputs/'.$outputName;
        $output = $this->absolute($outputPath);
        $this->ensureDirectory(dirname($output));
        @unlink($output);

        $args = ['--empty', '--pages'];
        foreach ($prepared as $path) {
            $args[] = $path;
            $args[] = '1-z';
        }
        $args[] = '--';
        $args[] = '--object-streams=generate';
        $args[] = '--linearize';
        $args[] = $output;

        try {
            $this->tools->execute('qpdf', $args);
        } finally {
            foreach ($cleanup as $path) {
                @unlink($path);
            }
        }

        if (! is_file($output) || filesize($output) <= 0) {
            throw new RuntimeException('No se pudo generar el PDF combinado.');
        }

        return [
            'output_name' => $outputName,
            'output_path' => $outputPath,
            'output_size' => (int) filesize($output),
        ];
    }

    /** @return array{result_files:array<int,array{name:string,path:string,size:int}>,warnings:array<int,string>} */
    public function split(PdfItem $item, array $settings): array
    {
        $this->tools->ensure(['qpdf']);
        $source = $this->decryptedInput($item);
        $mode = (string) ($settings['split_mode'] ?? 'each');
        $base = $this->safeBase($item->original_name);
        $relativeDir = $item->batch->basePath().'/outputs/'.$item->uuid;
        $absoluteDir = $this->absolute($relativeDir);
        $this->ensureDirectory($absoluteDir);
        foreach (glob($absoluteDir.'/*.pdf') ?: [] as $existing) {
            @unlink($existing);
        }
        $files = [];

        if ($mode === 'each' || $mode === 'every') {
            $every = $mode === 'every' ? max(1, (int) ($settings['split_every'] ?? 1)) : 1;
            $pattern = $absoluteDir.'/'.$base.'_pagina.pdf';
            $this->tools->execute('qpdf', [
                $source,
                '--split-pages='.$every,
                $pattern,
            ]);

            foreach (glob($absoluteDir.'/*.pdf') ?: [] as $path) {
                $files[] = $this->resultDescriptor($relativeDir, $path);
            }
        } else {
            $ranges = [];

            if ($mode === 'ranges') {
                $ranges = $this->parseRanges((string) ($settings['split_ranges'] ?? ''));
            } elseif ($mode === 'selected') {
                $selected = array_values(array_unique(array_map('intval', $settings['selected_pages'] ?? [])));
                sort($selected);
                if ($selected !== []) {
                    $ranges[] = implode(',', $selected);
                }
            }

            if ($ranges === []) {
                throw new RuntimeException('No se indicó un rango o páginas válidas para dividir.');
            }

            foreach ($ranges as $index => $range) {
                $name = $base.'_parte_'.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT).'.pdf';
                $path = $absoluteDir.'/'.$name;
                $this->tools->execute('qpdf', [
                    $source,
                    '--pages',
                    '.',
                    $range,
                    '--',
                    $path,
                ]);
                $files[] = $this->resultDescriptor($relativeDir, $path);
            }
        }

        $this->cleanupPreparedInput($item, $source);

        if ($files === []) {
            throw new RuntimeException('La división no produjo archivos de salida.');
        }

        usort($files, fn (array $a, array $b): int => strnatcasecmp($a['name'], $b['name']));

        return ['result_files' => $files, 'warnings' => []];
    }

    /** @return array{output_name:string,output_path:string,output_size:int} */
    public function reorder(PdfItem $item, array $settings): array
    {
        $this->tools->ensure(['qpdf']);
        $source = $this->decryptedInput($item);
        $plan = array_values(array_filter($settings['page_plan'] ?? [], function ($page): bool {
            return is_array($page) && (int) ($page['source'] ?? 0) > 0;
        }));

        if ($plan === []) {
            throw new RuntimeException('El orden de páginas está vacío.');
        }

        $range = implode(',', array_map(fn (array $page): int => (int) $page['source'], $plan));
        $base = $this->safeBase($item->original_name);
        $outputName = $base.'_ordenado.pdf';
        $outputPath = $item->batch->basePath().'/outputs/'.$outputName;
        $output = $this->absolute($outputPath);
        $this->ensureDirectory(dirname($output));
        @unlink($output);
        $args = [$source, '--pages', '.', $range, '--'];

        $rotations = [];
        foreach ($plan as $outputIndex => $page) {
            $rotation = ((int) ($page['rotation'] ?? 0) % 360 + 360) % 360;
            if (in_array($rotation, [90, 180, 270], true)) {
                $rotations[$rotation][] = $outputIndex + 1;
            }
        }

        foreach ($rotations as $rotation => $pages) {
            $args[] = '--rotate=+'.$rotation.':'.implode(',', $pages);
        }

        $args[] = '--object-streams=generate';
        $args[] = $output;
        $this->tools->execute('qpdf', $args);
        $this->cleanupPreparedInput($item, $source);
        $this->ensurePdfOutput($output, 'No se pudo generar el PDF ordenado.');

        return [
            'output_name' => $outputName,
            'output_path' => $outputPath,
            'output_size' => (int) filesize($output),
        ];
    }

    /** @return array{output_name:string,output_path:string,output_size:int} */
    public function security(PdfItem $item, array $settings, array $secret): array
    {
        $this->tools->ensure(['qpdf']);
        $mode = (string) ($settings['security_mode'] ?? 'protect');
        $source = $this->absolute($item->source_path);
        $base = $this->safeBase($item->original_name);
        $suffix = $mode === 'unlock' ? '_sin_contrasena' : '_protegido';
        $outputName = $base.$suffix.'.pdf';
        $outputPath = $item->batch->basePath().'/outputs/'.$outputName;
        $output = $this->absolute($outputPath);
        $this->ensureDirectory(dirname($output));
        @unlink($output);

        if ($mode === 'unlock') {
            $password = (string) ($item->secret ?? '');

            $this->tools->execute('qpdf', [
                $source,
                '--password='.$password,
                '--decrypt',
                $output,
            ]);
        } else {
            $userPassword = (string) ($secret['user_password'] ?? '');
            $ownerPassword = (string) ($secret['owner_password'] ?? '');
            if ($userPassword === '') {
                throw new RuntimeException('La nueva contraseña de apertura es obligatoria.');
            }
            if ($ownerPassword === '') {
                $ownerPassword = Str::random(32);
            }

            $args = [$source];
            if ($item->secret) {
                $args[] = '--password='.(string) $item->secret;
            }
            $args = array_merge($args, [
                '--encrypt',
                $userPassword,
                $ownerPassword,
                '256',
                '--print='.(string) ($settings['allow_print'] ?? 'full'),
                '--modify='.(string) ($settings['allow_modify'] ?? 'none'),
                '--extract='.(($settings['allow_extract'] ?? false) ? 'y' : 'n'),
                '--',
                $output,
            ]);
            $this->tools->execute('qpdf', $args);
        }

        $this->ensurePdfOutput($output, 'No se pudo generar el PDF de seguridad.');

        return [
            'output_name' => $outputName,
            'output_path' => $outputPath,
            'output_size' => (int) filesize($output),
        ];
    }


    private function ensurePdfOutput(string $path, string $message): void
    {
        if (! is_file($path) || (int) filesize($path) <= 0) {
            throw new RuntimeException($message);
        }
    }

    private function pageCount(string $source, string $password): int
    {
        $args = [];
        if ($password !== '') {
            $args[] = '-upw';
            $args[] = $password;
        }
        $args[] = $source;
        $process = $this->tools->execute('pdfinfo', $args, 120, false);

        if (! $process->isSuccessful()) {
            $message = trim($process->getErrorOutput().' '.$process->getOutput());
            if (str_contains(strtolower($message), 'password')) {
                throw new RuntimeException('El PDF está protegido. Escribe su contraseña para continuar.');
            }
            throw new RuntimeException($message ?: 'No se pudo leer la información del PDF.');
        }

        if (! preg_match('/^Pages:\s+(\d+)/mi', $process->getOutput(), $matches)) {
            throw new RuntimeException('No se pudo determinar el número de páginas.');
        }

        return max(1, (int) $matches[1]);
    }

    /** @return array<int,string> */
    private function generateThumbnails(PdfItem $item, string $source, string $password, int $pageCount): array
    {
        $fullPreview = in_array((string) $item->batch->operation, ['reorder', 'split'], true)
            && (int) $item->position === 1;
        $maxPages = $fullPreview
            ? min($pageCount, (int) config('system_pdf.max_preview_pages', 600))
            : 1;
        $relativeDir = $item->batch->basePath().'/thumbnails/'.$item->uuid;
        $absoluteDir = $this->absolute($relativeDir);
        $this->ensureDirectory($absoluteDir);
        foreach (glob($absoluteDir.'/*') ?: [] as $existing) {
            @unlink($existing);
        }

        $prefix = $absoluteDir.'/page';
        $args = ['-jpeg', '-r', (string) config('system_pdf.thumbnail_dpi', 30), '-f', '1', '-l', (string) $maxPages];
        if ($password !== '') {
            $args[] = '-upw';
            $args[] = $password;
        }
        $args[] = $source;
        $args[] = $prefix;
        $process = $this->tools->execute('pdftoppm', $args, 600, false);

        if (! $process->isSuccessful()) {
            $message = trim($process->getErrorOutput().' '.$process->getOutput());
            throw new RuntimeException($message ?: 'No se pudieron generar las miniaturas del PDF.');
        }

        $thumbs = [];
        $paths = glob($absoluteDir.'/page-*.jpg') ?: [];
        usort($paths, 'strnatcasecmp');
        foreach ($paths as $path) {
            $thumbs[] = $relativeDir.'/'.basename($path);
        }

        return $thumbs;
    }

    private function isEncrypted(string $source): bool
    {
        $process = $this->tools->execute('qpdf', ['--is-encrypted', $source], 30, false);
        return $process->getExitCode() === 0;
    }

    private function decryptedInput(PdfItem $item): string
    {
        $source = $this->absolute($item->source_path);
        if (! $item->encrypted) {
            return $source;
        }

        $password = (string) ($item->secret ?? '');

        $temporary = $this->absolute($item->batch->basePath().'/work/'.$item->uuid.'-decrypted.pdf');
        $this->ensureDirectory(dirname($temporary));
        $this->tools->execute('qpdf', [
            $source,
            '--password='.$password,
            '--decrypt',
            $temporary,
        ]);

        return $temporary;
    }

    private function cleanupPreparedInput(PdfItem $item, string $path): void
    {
        if ($item->source_path && $path !== $this->absolute($item->source_path)) {
            @unlink($path);
        }
    }

    private function imageToPdf(PdfItem $item): string
    {
        $source = $this->absolute($item->source_path);
        $mime = $item->mime ?: 'image/jpeg';
        $data = base64_encode((string) file_get_contents($source));
        $html = '<!doctype html><html><head><style>@page{margin:0;size:A4 portrait}html,body{margin:0;width:100%;height:100%}.page{width:100%;height:100%;display:flex;align-items:center;justify-content:center}.page img{max-width:100%;max-height:100%;object-fit:contain}</style></head><body><div class="page"><img src="data:'
            .htmlspecialchars($mime, ENT_QUOTES, 'UTF-8').';base64,'.$data.'"></div></body></html>';
        $target = $this->absolute($item->batch->basePath().'/work/'.$item->uuid.'-image.pdf');
        $this->ensureDirectory(dirname($target));
        Pdf::loadHTML($html)->setPaper('a4', 'portrait')->save($target);
        unset($data, $html);

        return $target;
    }

    /** @return array<int,string> */
    private function parseRanges(string $value): array
    {
        $ranges = [];
        foreach (preg_split('/[;\n]+/', $value) ?: [] as $group) {
            $tokens = [];
            foreach (explode(',', $group) as $token) {
                $token = trim($token);
                if (preg_match('/^\d+(?:-\d+)?$/', $token)) {
                    $tokens[] = $token;
                }
            }
            if ($tokens !== []) {
                $ranges[] = implode(',', $tokens);
            }
        }

        return $ranges;
    }

    /** @return array{name:string,path:string,size:int} */
    private function resultDescriptor(string $relativeDir, string $absolutePath): array
    {
        return [
            'name' => basename($absolutePath),
            'path' => $relativeDir.'/'.basename($absolutePath),
            'size' => (int) filesize($absolutePath),
        ];
    }

    private function absolute(string $relativePath): string
    {
        return Storage::disk('local')->path($relativePath);
    }

    private function ensureDirectory(string $directory): void
    {
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('No se pudo crear el directorio de trabajo para PDF.');
        }
    }

    private function safeBase(string $name): string
    {
        $base = pathinfo($name, PATHINFO_FILENAME);
        $base = Str::ascii($base);
        $base = preg_replace('/[^A-Za-z0-9_-]+/', '_', $base) ?: 'documento';
        return trim(substr($base, 0, 100), '_-') ?: 'documento';
    }

    private function isImage(PdfItem $item): bool
    {
        return str_starts_with((string) $item->mime, 'image/')
            || in_array(strtolower((string) $item->extension), ['jpg', 'jpeg', 'png', 'webp'], true);
    }
}
