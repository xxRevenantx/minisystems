<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use RuntimeException;

class SimpleZipService
{
    /**
     * Crea un ZIP compatible sin depender de la extensión php-zip.
     *
     * @param array<int,array{name:string,path:string}> $entries
     */
    public function createFromStorage(string $targetPath, FilesystemAdapter $disk, array $entries): void
    {
        $directory = dirname($targetPath);

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('No fue posible crear el directorio temporal para el ZIP.');
        }

        $handle = @fopen($targetPath, 'wb');

        if (! is_resource($handle)) {
            throw new RuntimeException('No fue posible crear el archivo ZIP temporal.');
        }

        $centralDirectory = [];
        $offset = 0;

        try {
            foreach ($entries as $entry) {
                $name = $this->safeEntryName($entry['name']);
                $contents = $disk->get($entry['path']);
                $uncompressedSize = strlen($contents);
                $crc = crc32($contents);
                $compressed = function_exists('gzdeflate') ? gzdeflate($contents, 6) : false;

                if (is_string($compressed) && strlen($compressed) < $uncompressedSize) {
                    $method = 8;
                    $payload = $compressed;
                } else {
                    $method = 0;
                    $payload = $contents;
                }

                $compressedSize = strlen($payload);
                [$dosTime, $dosDate] = $this->dosDateTime();
                $flags = 0x0800;
                $nameLength = strlen($name);
                $localOffset = $offset;

                $localHeader = pack(
                    'VvvvvvVVVvv',
                    0x04034b50,
                    20,
                    $flags,
                    $method,
                    $dosTime,
                    $dosDate,
                    $crc,
                    $compressedSize,
                    $uncompressedSize,
                    $nameLength,
                    0
                );

                $this->write($handle, $localHeader);
                $this->write($handle, $name);
                $this->write($handle, $payload);
                $offset += strlen($localHeader) + $nameLength + $compressedSize;

                $centralDirectory[] = pack(
                    'VvvvvvvVVVvvvvvVV',
                    0x02014b50,
                    20,
                    20,
                    $flags,
                    $method,
                    $dosTime,
                    $dosDate,
                    $crc,
                    $compressedSize,
                    $uncompressedSize,
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

            foreach ($centralDirectory as $centralEntry) {
                $this->write($handle, $centralEntry);
                $centralSize += strlen($centralEntry);
            }

            $entryCount = count($centralDirectory);
            $endRecord = pack(
                'VvvvvVVv',
                0x06054b50,
                0,
                0,
                $entryCount,
                $entryCount,
                $centralSize,
                $centralOffset,
                0
            );
            $this->write($handle, $endRecord);
        } catch (\Throwable $exception) {
            fclose($handle);
            @unlink($targetPath);
            throw $exception;
        }

        fclose($handle);
    }

    private function safeEntryName(string $name): string
    {
        $name = str_replace('\\', '/', $name);
        $name = basename($name);
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?: 'imagen';

        return substr($name, 0, 180);
    }

    /** @return array{0:int,1:int} */
    private function dosDateTime(): array
    {
        $now = getdate();
        $year = max(1980, (int) $now['year']);
        $dosTime = ((int) $now['hours'] << 11) | ((int) $now['minutes'] << 5) | ((int) $now['seconds'] >> 1);
        $dosDate = (($year - 1980) << 9) | ((int) $now['mon'] << 5) | (int) $now['mday'];

        return [$dosTime, $dosDate];
    }

    /** @param resource $handle */
    private function write($handle, string $contents): void
    {
        $length = strlen($contents);
        $written = 0;

        while ($written < $length) {
            $bytes = fwrite($handle, substr($contents, $written));

            if ($bytes === false || $bytes === 0) {
                throw new RuntimeException('No fue posible escribir el archivo ZIP.');
            }

            $written += $bytes;
        }
    }
}
