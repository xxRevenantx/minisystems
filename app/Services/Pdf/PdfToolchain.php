<?php

namespace App\Services\Pdf;

use RuntimeException;
use Symfony\Component\Process\Process;

class PdfToolchain
{
    /** @var array<string,string|null> */
    private array $resolved = [];

    /** @return array<string,array{available:bool,path:?string,version:?string}> */
    public function status(): array
    {
        $tools = [];

        foreach (['ghostscript', 'qpdf', 'pdfinfo', 'pdftoppm'] as $tool) {
            $path = $this->resolve($tool);
            $version = null;

            if ($path) {
                try {
                    $arguments = $tool === 'ghostscript' ? ['--version'] : ['-v'];
                    if ($tool === 'qpdf') {
                        $arguments = ['--version'];
                    }
                    if (in_array($tool, ['pdfinfo', 'pdftoppm'], true)) {
                        $arguments = ['-v'];
                    }

                    $process = $this->execute($tool, $arguments, 15, false);
                    $version = trim($process->getOutput().' '.$process->getErrorOutput()) ?: null;
                } catch (\Throwable) {
                    $version = null;
                }
            }

            $tools[$tool] = [
                'available' => $path !== null,
                'path' => $path,
                'version' => $version,
            ];
        }

        return $tools;
    }

    public function ensure(array $tools): void
    {
        $missing = array_values(array_filter($tools, fn (string $tool): bool => $this->resolve($tool) === null));

        if ($missing !== []) {
            throw new RuntimeException(
                'Faltan herramientas requeridas: '.implode(', ', $missing).'. '
                .'Ejecuta php artisan system-pdf:check para ver la configuración de Laragon.'
            );
        }
    }

    public function execute(
        string $tool,
        array $arguments,
        ?int $timeout = null,
        bool $throw = true,
    ): Process {
        $binary = $this->resolve($tool);

        if (! $binary) {
            throw new RuntimeException("No se encontró el ejecutable de {$tool}.");
        }

        $process = new Process(array_merge([$binary], array_values($arguments)));
        $process->setTimeout($timeout ?? (int) config('system_pdf.job_timeout', 3600));
        $process->run();

        if ($throw && ! $process->isSuccessful()) {
            $message = trim($process->getErrorOutput().' '.$process->getOutput());
            throw new RuntimeException($message !== '' ? $message : "Falló la ejecución de {$tool}.");
        }

        return $process;
    }

    public function path(string $tool): string
    {
        return $this->resolve($tool)
            ?? throw new RuntimeException("No se encontró el ejecutable de {$tool}.");
    }

    private function resolve(string $tool): ?string
    {
        if (array_key_exists($tool, $this->resolved)) {
            return $this->resolved[$tool];
        }

        $configured = trim((string) config("system_pdf.binaries.{$tool}", ''));
        if ($configured !== '' && is_file($configured)) {
            return $this->resolved[$tool] = $configured;
        }

        foreach ($this->candidates($tool) as $candidate) {
            if (is_file($candidate)) {
                return $this->resolved[$tool] = $candidate;
            }
        }

        $command = PHP_OS_FAMILY === 'Windows' ? 'where' : 'which';
        $names = match ($tool) {
            'ghostscript' => PHP_OS_FAMILY === 'Windows'
                ? ['gswin64c.exe', 'gswin32c.exe', 'gs.exe']
                : ['gs'],
            'qpdf' => PHP_OS_FAMILY === 'Windows' ? ['qpdf.exe'] : ['qpdf'],
            'pdfinfo' => PHP_OS_FAMILY === 'Windows' ? ['pdfinfo.exe'] : ['pdfinfo'],
            'pdftoppm' => PHP_OS_FAMILY === 'Windows' ? ['pdftoppm.exe'] : ['pdftoppm'],
            default => [],
        };

        foreach ($names as $name) {
            try {
                $process = new Process([$command, $name]);
                $process->setTimeout(5);
                $process->run();
                if ($process->isSuccessful()) {
                    $first = trim(strtok($process->getOutput(), "\r\n") ?: '');
                    if ($first !== '' && is_file($first)) {
                        return $this->resolved[$tool] = $first;
                    }
                }
            } catch (\Throwable) {
                // Continúa con el siguiente candidato.
            }
        }

        return $this->resolved[$tool] = null;
    }

    /** @return array<int,string> */
    private function candidates(string $tool): array
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return match ($tool) {
                'ghostscript' => ['/usr/bin/gs', '/usr/local/bin/gs'],
                'qpdf' => ['/usr/bin/qpdf', '/usr/local/bin/qpdf'],
                'pdfinfo' => ['/usr/bin/pdfinfo', '/usr/local/bin/pdfinfo'],
                'pdftoppm' => ['/usr/bin/pdftoppm', '/usr/local/bin/pdftoppm'],
                default => [],
            };
        }

        $programFiles = array_filter([
            getenv('ProgramFiles') ?: null,
            getenv('ProgramFiles(x86)') ?: null,
            'C:\\Program Files',
            'C:\\Program Files (x86)',
        ]);
        $candidates = [];

        foreach ($programFiles as $root) {
            if ($tool === 'ghostscript') {
                $matches = glob($root.'\\gs\\gs*\\bin\\gswin64c.exe') ?: [];
                rsort($matches, SORT_NATURAL);
                $candidates = array_merge($candidates, $matches);
            }

            if ($tool === 'qpdf') {
                foreach ([$root.'\\qpdf\\bin\\qpdf.exe', $root.'\\qpdf*\\bin\\qpdf.exe'] as $pattern) {
                    $matches = glob($pattern) ?: [];
                    rsort($matches, SORT_NATURAL);
                    $candidates = array_merge($candidates, $matches);
                }
            }
        }

        $popplerRoots = [
            'C:\\poppler\\Library\\bin',
            'C:\\tools\\poppler\\Library\\bin',
            'C:\\laragon\\bin\\poppler\\Library\\bin',
            'C:\\laragon\\bin\\poppler\\bin',
        ];

        if (in_array($tool, ['pdfinfo', 'pdftoppm'], true)) {
            $file = $tool.'.exe';
            foreach ($popplerRoots as $root) {
                $candidates[] = $root.'\\'.$file;
            }
        }

        return array_values(array_unique($candidates));
    }
}
