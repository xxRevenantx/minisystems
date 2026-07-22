<?php

namespace App\Services;

class ZipPartPlanner
{
    /**
     * Agrupa entradas de ZIP en partes pequeñas para evitar descargas demasiado pesadas.
     *
     * @param array<int,array{name:string,path:string,size?:int}> $entries
     * @return array<int,array{number:int,file_count:int,bytes:int,entries:array<int,array{name:string,path:string,size?:int}>}>
     */
    public function plan(array $entries, int $maxFiles = 100, int $maxBytes = 524288000): array
    {
        $maxFiles = max(1, $maxFiles);
        $maxBytes = max(1, $maxBytes);
        $parts = [];
        $current = [];
        $currentBytes = 0;

        foreach ($entries as $entry) {
            $size = max(0, (int) ($entry['size'] ?? 0));
            $wouldExceedCount = count($current) >= $maxFiles;
            $wouldExceedBytes = $current !== [] && ($currentBytes + $size) > $maxBytes;

            if ($wouldExceedCount || $wouldExceedBytes) {
                $parts[] = $this->makePart(count($parts) + 1, $current, $currentBytes);
                $current = [];
                $currentBytes = 0;
            }

            $current[] = $entry;
            $currentBytes += $size;
        }

        if ($current !== []) {
            $parts[] = $this->makePart(count($parts) + 1, $current, $currentBytes);
        }

        return $parts;
    }

    /**
     * @param array<int,array{name:string,path:string,size?:int}> $entries
     * @return array{number:int,file_count:int,bytes:int,entries:array<int,array{name:string,path:string,size?:int}>}
     */
    private function makePart(int $number, array $entries, int $bytes): array
    {
        return [
            'number' => $number,
            'file_count' => count($entries),
            'bytes' => $bytes,
            'entries' => array_values($entries),
        ];
    }
}
