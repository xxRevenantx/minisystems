<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

class EtiquetaXlsxService
{
    public const HEADERS = ['nombre', 'nivel', 'generacion', 'grado', 'grupo', 'estado'];

    public function crearPlantilla(string $ruta): void
    {
        $filas = [
            self::HEADERS,
            ['MARÍA PÉREZ LÓPEZ', 'Primaria', '2023-2029', '3°', 'A', 'activo'],
            ['ANA RAMÍREZ DÍAZ', 'Personal', '', '', '', 'activo'],
        ];

        $catalogos = [
            ['NIVELES DISPONIBLES', 'ESTADOS', 'INSTRUCCIONES'],
            ['Preescolar', 'activo', 'Nombre y nivel son obligatorios. Generación no es obligatoria para Personal y Otro.'],
            ['Primaria', 'inactivo', 'Para Personal y Otro, generación, grado y grupo no se imprimen en el PDF.'],
            ['Secundaria', '', 'No cambies los encabezados de la primera fila.'],
            ['Bachillerato', '', 'Los duplicados se omiten usando nombre, nivel, generación, grado y grupo.'],
            ['Licenciatura', '', 'Puedes agregar tantos registros como necesites.'],
            ['Personal', '', ''],
            ['Curso', '', ''],
            ['Taller', '', ''],
            ['Otro', '', ''],
        ];

        $this->crearLibro($ruta, [
            ['nombre' => 'Alumnos', 'filas' => $filas, 'validarNivel' => true],
            ['nombre' => 'Catalogos', 'filas' => $catalogos, 'validarNivel' => false],
        ]);
    }

    public function crearExportacion(string $ruta, iterable $alumnos): void
    {
        $filas = [self::HEADERS];
        foreach ($alumnos as $alumno) {
            $filas[] = [
                $alumno->nombre,
                $alumno->nivel,
                $alumno->generacion,
                $alumno->grado,
                $alumno->grupo,
                $alumno->activo ? 'activo' : 'inactivo',
            ];
        }
        $this->crearLibro($ruta, [['nombre' => 'Alumnos', 'filas' => $filas, 'validarNivel' => false]]);
    }

    public function leer(string $ruta): array
    {
        $this->asegurarZip();
        $archivo = $this->abrirArchivoLectura($ruta);

        $sharedStrings = [];
        $sharedXml = $this->leerEntrada($archivo, 'xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            preg_match_all('/<si>(.*?)<\/si>/s', $sharedXml, $items);
            foreach ($items[1] as $item) {
                preg_match_all('/<t[^>]*>(.*?)<\/t>/s', $item, $texts);
                $sharedStrings[] = html_entity_decode(implode('', $texts[1]), ENT_QUOTES | ENT_XML1, 'UTF-8');
            }
        }

        $sheetXml = $this->leerEntrada($archivo, 'xl/worksheets/sheet1.xml');
        if ($archivo instanceof ZipArchive) $archivo->close();
        if ($sheetXml === false) {
            throw new RuntimeException('El archivo no contiene la hoja principal esperada.');
        }

        preg_match_all('/<row[^>]*>(.*?)<\/row>/s', $sheetXml, $rowMatches);
        $rows = [];
        foreach ($rowMatches[1] as $rowXml) {
            $row = [];
            preg_match_all('/<c\b([^>]*)>(.*?)<\/c>/s', $rowXml, $cells, PREG_SET_ORDER);
            foreach ($cells as $cell) {
                $attrs = $cell[1];
                $body = $cell[2];
                preg_match('/\br="([A-Z]+)\d+"/', $attrs, $refMatch);
                $column = $this->columnaAIndice($refMatch[1] ?? 'A');
                $type = preg_match('/\bt="([^"]+)"/', $attrs, $typeMatch) ? $typeMatch[1] : '';
                $value = '';
                if ($type === 'inlineStr' && preg_match_all('/<t[^>]*>(.*?)<\/t>/s', $body, $textMatch)) {
                    $value = implode('', $textMatch[1]);
                } elseif (preg_match('/<v>(.*?)<\/v>/s', $body, $valueMatch)) {
                    $raw = $valueMatch[1];
                    $value = $type === 's' ? ($sharedStrings[(int) $raw] ?? '') : $raw;
                }
                $row[$column] = trim(html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_XML1, 'UTF-8'));
            }
            if ($row) {
                $max = max(array_keys($row));
                $rows[] = array_replace(array_fill(0, $max + 1, ''), $row);
            }
        }
        return $rows;
    }

    private function crearLibro(string $ruta, array $hojas): void
    {
        $this->asegurarZip();
        @unlink($ruta);

        if (class_exists(ZipArchive::class)) {
            $archivo = new ZipArchive();
            if ($archivo->open($ruta, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('No fue posible crear el archivo Excel.');
            }
        } else {
            $archivo = new \PharData($ruta, 0, null, \Phar::ZIP);
        }

        $archivo->addFromString('[Content_Types].xml', $this->contentTypes(count($hojas)));
        $archivo->addFromString('_rels/.rels', $this->rootRels());
        $archivo->addFromString('xl/workbook.xml', $this->workbook($hojas));
        $archivo->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels(count($hojas)));
        $archivo->addFromString('xl/styles.xml', $this->styles());
        foreach ($hojas as $index => $hoja) {
            $archivo->addFromString('xl/worksheets/sheet'.($index + 1).'.xml', $this->worksheet($hoja['filas'], (bool) ($hoja['validarNivel'] ?? false)));
        }
        if ($archivo instanceof ZipArchive) $archivo->close();
        unset($archivo);
    }

    private function worksheet(array $rows, bool $validarNivel): string
    {
        $sheetRows = '';
        foreach ($rows as $rIndex => $row) {
            $number = $rIndex + 1;
            $cells = '';
            foreach (array_values($row) as $cIndex => $value) {
                $ref = $this->indiceAColumna($cIndex).$number;
                $style = $number === 1 ? ' s="1"' : '';
                $safe = htmlspecialchars((string) ($value ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $cells .= '<c r="'.$ref.'" t="inlineStr"'.$style.'><is><t xml:space="preserve">'.$safe.'</t></is></c>';
            }
            $sheetRows .= '<row r="'.$number.'">'.$cells.'</row>';
        }
        $validation = $validarNivel
            ? '<dataValidations count="2"><dataValidation type="list" allowBlank="0" showErrorMessage="1" errorTitle="Nivel inválido" error="Selecciona un nivel del catálogo." sqref="B2:B5000"><formula1>"Preescolar,Primaria,Secundaria,Bachillerato,Licenciatura,Personal,Curso,Taller,Otro"</formula1></dataValidation><dataValidation type="list" allowBlank="1" showErrorMessage="1" sqref="F2:F5000"><formula1>"activo,inactivo"</formula1></dataValidation></dataValidations>'
            : '';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            .'<cols><col min="1" max="1" width="38" customWidth="1"/><col min="2" max="3" width="20" customWidth="1"/><col min="4" max="6" width="16" customWidth="1"/></cols>'
            .'<sheetData>'.$sheetRows.'</sheetData><autoFilter ref="A1:F1"/>'.$validation.'</worksheet>';
    }

    private function contentTypes(int $count): string
    {
        $sheets = '';
        for ($i=1; $i <= $count; $i++) $sheets .= '<Override PartName="/xl/worksheets/sheet'.$i.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'.$sheets.'</Types>';
    }

    private function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
    }

    private function workbook(array $hojas): string
    {
        $sheets = '';
        foreach ($hojas as $i => $hoja) $sheets .= '<sheet name="'.htmlspecialchars($hoja['nombre'], ENT_XML1 | ENT_QUOTES, 'UTF-8').'" sheetId="'.($i+1).'" r:id="rId'.($i+1).'"/>';
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>'.$sheets.'</sheets></workbook>';
    }

    private function workbookRels(int $count): string
    {
        $rels = '';
        for ($i=1; $i <= $count; $i++) $rels .= '<Relationship Id="rId'.$i.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$i.'.xml"/>';
        $rels .= '<Relationship Id="rId'.($count+1).'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.$rels.'</Relationships>';
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF006492"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center"/></xf></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles><dxfs count="0"/><tableStyles count="0" defaultTableStyle="TableStyleMedium2" defaultPivotStyle="PivotStyleLight16"/></styleSheet>';
    }

    private function asegurarZip(): void
    {
        if (! class_exists(ZipArchive::class) && ! class_exists(\PharData::class)) {
            throw new RuntimeException('El servidor no dispone de soporte ZIP/Phar para trabajar con archivos .xlsx.');
        }
    }

    private function abrirArchivoLectura(string $ruta): object
    {
        if (class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();
            if ($zip->open($ruta) !== true) throw new RuntimeException('No fue posible abrir el archivo Excel.');
            return $zip;
        }

        try {
            return new \PharData($ruta, 0, null, \Phar::ZIP);
        } catch (\Throwable $e) {
            throw new RuntimeException('No fue posible abrir el archivo Excel. Verifica que sea un .xlsx válido.', previous: $e);
        }
    }

    private function leerEntrada(object $archivo, string $nombre): string|false
    {
        if ($archivo instanceof ZipArchive) return $archivo->getFromName($nombre);
        return isset($archivo[$nombre]) ? $archivo[$nombre]->getContent() : false;
    }

    private function indiceAColumna(int $index): string
    {
        $column = '';
        for ($n = $index + 1; $n > 0; $n = intdiv($n - 1, 26)) $column = chr(65 + (($n - 1) % 26)).$column;
        return $column;
    }

    private function columnaAIndice(string $column): int
    {
        $result = 0;
        foreach (str_split($column) as $char) $result = $result * 26 + (ord($char) - 64);
        return max(0, $result - 1);
    }
}
