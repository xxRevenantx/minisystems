<?php

namespace App\Services\Pdf;

use Illuminate\Filesystem\FilesystemAdapter;
use RuntimeException;
use ZipArchive;

class PdfZipService
{
    /**
     * @param array<int,array{name:string,path:string}> $entries
     */
    public function create(string $targetPath, FilesystemAdapter $disk, array $entries): void
    {
        $directory = dirname($targetPath);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('No se pudo crear el directorio temporal del ZIP.');
        }

        if (class_exists(ZipArchive::class)) {
            $this->createWithZipArchive($targetPath, $disk, $entries);
            return;
        }

        $this->createStoredZip($targetPath, $disk, $entries);
    }

    private function createWithZipArchive(string $targetPath, FilesystemAdapter $disk, array $entries): void
    {
        $zip = new ZipArchive();
        if ($zip->open($targetPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No fue posible crear el archivo ZIP.');
        }

        try {
            foreach ($entries as $entry) {
                $absolute = $disk->path($entry['path']);
                if (! is_file($absolute)) {
                    continue;
                }
                $zip->addFile($absolute, $this->safeName($entry['name']));
                $zip->setCompressionName($this->safeName($entry['name']), ZipArchive::CM_STORE);
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * ZIP sin compresión para no cargar PDF grandes en memoria.
     * @param array<int,array{name:string,path:string}> $entries
     */
    private function createStoredZip(string $targetPath, FilesystemAdapter $disk, array $entries): void
    {
        $out = fopen($targetPath, 'wb');
        if (! is_resource($out)) {
            throw new RuntimeException('No fue posible abrir el ZIP para escritura.');
        }

        $central = [];
        $offset = 0;

        try {
            foreach ($entries as $entry) {
                $absolute = $disk->path($entry['path']);
                if (! is_file($absolute)) {
                    continue;
                }

                $name = $this->safeName($entry['name']);
                $size = (int) filesize($absolute);
                if ($size > 0xFFFFFFFF) {
                    throw new RuntimeException('Un archivo supera 4 GB. Reduce el tamaño del lote ZIP.');
                }

                $crcHex = hash_file('crc32b', $absolute);
                $crc = (int) hexdec($crcHex ?: '0');
                [$dosTime, $dosDate] = $this->dosDateTime((int) filemtime($absolute));
                $nameLength = strlen($name);
                $localOffset = $offset;
                $header = pack(
                    'VvvvvvVVVvv',
                    0x04034b50,
                    20,
                    0x0800,
                    0,
                    $dosTime,
                    $dosDate,
                    $crc,
                    $size,
                    $size,
                    $nameLength,
                    0
                );
                $this->write($out, $header.$name);
                $offset += strlen($header) + $nameLength;

                $in = fopen($absolute, 'rb');
                if (! is_resource($in)) {
                    throw new RuntimeException("No fue posible leer {$name}.");
                }
                while (! feof($in)) {
                    $chunk = fread($in, 1024 * 1024);
                    if ($chunk === false) {
                        fclose($in);
                        throw new RuntimeException("Falló la lectura de {$name}.");
                    }
                    if ($chunk !== '') {
                        $this->write($out, $chunk);
                        $offset += strlen($chunk);
                    }
                }
                fclose($in);

                $central[] = pack(
                    'VvvvvvvVVVvvvvvVV',
                    0x02014b50,
                    20,
                    20,
                    0x0800,
                    0,
                    $dosTime,
                    $dosDate,
                    $crc,
                    $size,
                    $size,
                    $nameLength,
                    0,
                    0,
                    0,
                    0,
                    0,
                    $localOffset
                ).$name;
            }

            $centralOffset = $offset;
            $centralSize = 0;
            foreach ($central as $record) {
                $this->write($out, $record);
                $centralSize += strlen($record);
            }

            $count = count($central);
            $this->write($out, pack(
                'VvvvvVVv',
                0x06054b50,
                0,
                0,
                $count,
                $count,
                $centralSize,
                $centralOffset,
                0
            ));
        } catch (\Throwable $exception) {
            fclose($out);
            @unlink($targetPath);
            throw $exception;
        }

        fclose($out);
    }

    private function safeName(string $name): string
    {
        $name = str_replace('\\', '/', $name);
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?: 'documento.pdf';
        $segments = array_values(array_filter(explode('/', $name), fn (string $part): bool => ! in_array($part, ['', '.', '..'], true)));
        $segments = array_map(function (string $part): string {
            $part = preg_replace('/[^A-Za-z0-9._ -]/u', '_', $part) ?: 'archivo';
            return substr(trim($part, ' .') ?: 'archivo', 0, 150);
        }, $segments);
        return substr(implode('/', $segments) ?: 'documento.pdf', 0, 240);
    }

    /** @return array{0:int,1:int} */
    private function dosDateTime(int $timestamp): array
    {
        $date = getdate($timestamp ?: time());
        $year = max(1980, (int) $date['year']);
        return [
            ((int) $date['hours'] << 11) | ((int) $date['minutes'] << 5) | ((int) $date['seconds'] >> 1),
            (($year - 1980) << 9) | ((int) $date['mon'] << 5) | (int) $date['mday'],
        ];
    }

    /** @param resource $handle */
    private function write($handle, string $data): void
    {
        $length = strlen($data);
        $written = 0;
        while ($written < $length) {
            $bytes = fwrite($handle, substr($data, $written));
            if ($bytes === false || $bytes === 0) {
                throw new RuntimeException('No fue posible escribir el archivo ZIP.');
            }
            $written += $bytes;
        }
    }
}
