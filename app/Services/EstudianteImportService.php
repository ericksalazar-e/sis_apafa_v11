<?php

namespace App\Services;

use App\Models\Estudiante;
use App\Models\PadreFamilia;
use Illuminate\Http\UploadedFile;
use ZipArchive;
use SimpleXMLElement;

class EstudianteImportService
{
    /**
     * Genera y retorna el contenido binario de un archivo Excel (.xlsx) válido como plantilla.
     */
    public function generateTemplateXlsx(): string
    {
        $headers = [
            'DNI',
            'NOMBRES',
            'APELLIDO_PATERNO',
            'APELLIDO_MATERNO',
            'NIVEL',
            'GRADO',
            'SECCION',
            'TURNO',
            'DNI_APODERADO',
            'PARENTESCO',
        ];

        $sampleRows = [
            ['71234501', 'CARLOS DANIEL', 'QUISPE', 'FLORES', 'Primaria', '1ro', 'A', 'Mañana', '45678901', 'HIJO(A)'],
            ['71234502', 'VALERIA SOFIA', 'MENDOZA', 'TORRES', 'Primaria', '2do', 'B', 'Mañana', '45678902', 'HIJO(A)'],
            ['71234503', 'MIGUEL ANGEL', 'RODRIGUEZ', 'CASTILLO', 'Secundaria', '1ro', 'A', 'Tarde', '', 'HIJO(A)'],
            ['71234504', 'LUCIA BEATRIZ', 'SANCHEZ', 'VARGAS', 'Inicial', '4 Años', 'Única', 'Mañana', '', 'HIJO(A)'],
            ['71234505', 'JUAN PABLO', 'HERRERA', 'GUZMAN', 'Secundaria', '3ro', 'C', 'Mañana', '50306858', 'TUTOR / APODERADO'],
        ];

        $tempFile = tempnam(sys_get_temp_dir(), 'tpl_xlsx_');
        $this->createXlsxFile($tempFile, $headers, $sampleRows);
        $content = file_get_contents($tempFile);
        @unlink($tempFile);

        return $content;
    }

    /**
     * Genera y retorna el contenido de una plantilla CSV con codificación UTF-8 y BOM para Excel.
     */
    public function generateTemplateCsv(): string
    {
        $headers = [
            'DNI',
            'NOMBRES',
            'APELLIDO_PATERNO',
            'APELLIDO_MATERNO',
            'NIVEL',
            'GRADO',
            'SECCION',
            'TURNO',
            'DNI_APODERADO',
            'PARENTESCO',
        ];

        $sampleRows = [
            ['71234501', 'CARLOS DANIEL', 'QUISPE', 'FLORES', 'Primaria', '1ro', 'A', 'Mañana', '45678901', 'HIJO(A)'],
            ['71234502', 'VALERIA SOFIA', 'MENDOZA', 'TORRES', 'Primaria', '2do', 'B', 'Mañana', '45678902', 'HIJO(A)'],
            ['71234503', 'MIGUEL ANGEL', 'RODRIGUEZ', 'CASTILLO', 'Secundaria', '1ro', 'A', 'Tarde', '', 'HIJO(A)'],
            ['71234504', 'LUCIA BEATRIZ', 'SANCHEZ', 'VARGAS', 'Inicial', '4 Años', 'Única', 'Mañana', '', 'HIJO(A)'],
            ['71234505', 'JUAN PABLO', 'HERRERA', 'GUZMAN', 'Secundaria', '3ro', 'C', 'Mañana', '50306858', 'TUTOR / APODERADO'],
        ];

        $output = fopen('php://memory', 'r+');
        // Escribir BOM UTF-8 para que Excel en Windows reconozca caracteres con tildes y ñ sin problemas
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, $headers, ';');
        foreach ($sampleRows as $row) {
            fputcsv($output, $row, ';');
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Procesa la importación de un archivo Excel (.xlsx) o CSV subido.
     */
    public function import(UploadedFile $file, int $empresaId, array $defaults = []): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $realPath = $file->getRealPath();

        $rows = [];
        if (in_array($extension, ['xlsx', 'xlsm'])) {
            $rows = $this->parseXlsx($realPath);
        } else {
            $rows = $this->parseCsv($realPath);
        }

        if (empty($rows)) {
            return [
                'total' => 0,
                'creados' => 0,
                'actualizados' => 0,
                'con_apoderado' => 0,
                'sin_apoderado' => 0,
                'errores' => ['El archivo cargado está vacío o no contiene filas con datos válidos.'],
            ];
        }

        // Fila 0: Encabezados
        $headerRow = array_shift($rows);
        $columnMap = $this->mapHeaders($headerRow);

        if (!isset($columnMap['dni']) || !isset($columnMap['nombres'])) {
            return [
                'total' => 0,
                'creados' => 0,
                'actualizados' => 0,
                'con_apoderado' => 0,
                'sin_apoderado' => 0,
                'errores' => ['Las columnas obligatorias "DNI" y "NOMBRES" no fueron encontradas en la primera fila del archivo.'],
            ];
        }

        $creados = 0;
        $actualizados = 0;
        $conApoderado = 0;
        $sinApoderado = 0;
        $errores = [];

        // Cache de apoderados por DNI para agilizar consultas
        $padresCache = PadreFamilia::where('empresa_id', $empresaId)
            ->get(['id', 'padre_dni', 'padre_nombres', 'padre_ape_pat'])
            ->keyBy(fn($p) => trim($p->padre_dni));

        $filaNumero = 1; // 1-indexed (fila 1 eran encabezados)

        foreach ($rows as $row) {
            $filaNumero++;

            $dniRaw = $this->getVal($row, $columnMap['dni'] ?? null);
            if (empty($dniRaw)) {
                // Fila vacía, ignorar
                continue;
            }

            // Normalizar DNI (8 dígitos)
            $dni = preg_replace('/[^\d]/', '', $dniRaw);
            if (strlen($dni) < 8) {
                $dni = str_pad($dni, 8, '0', STR_PAD_LEFT);
            }

            if (strlen($dni) !== 8) {
                $errores[] = "Fila #{$filaNumero}: El DNI '{$dniRaw}' no es válido (debe tener 8 dígitos).";
                continue;
            }

            $nombres = trim($this->getVal($row, $columnMap['nombres'] ?? null));
            if (empty($nombres)) {
                $errores[] = "Fila #{$filaNumero} (DNI {$dni}): El campo NOMBRES es obligatorio.";
                continue;
            }

            $apePat = trim($this->getVal($row, $columnMap['ape_pat'] ?? null));
            $apeMat = trim($this->getVal($row, $columnMap['ape_mat'] ?? null));

            // Si vino una sola columna de apellidos combinados
            if (empty($apePat) && isset($columnMap['apellidos'])) {
                $apellidosCombined = trim($this->getVal($row, $columnMap['apellidos']));
                $partesApe = explode(' ', $apellidosCombined, 2);
                $apePat = $partesApe[0] ?? '';
                $apeMat = $partesApe[1] ?? '';
            }

            if (empty($apePat)) {
                $apePat = 'NO ESPECIFICADO';
            }

            $nivel = $this->normalizarNivel($this->getVal($row, $columnMap['nivel'] ?? null, $defaults['nivel'] ?? 'Primaria'));
            $grado = $this->normalizarGrado($this->getVal($row, $columnMap['grado'] ?? null, $defaults['grado'] ?? '1ro'));
            $seccion = $this->normalizarSeccion($this->getVal($row, $columnMap['seccion'] ?? null, $defaults['seccion'] ?? 'A'));
            $turno = $this->normalizarTurno($this->getVal($row, $columnMap['turno'] ?? null, $defaults['turno'] ?? 'Mañana'));

            $parentesco = trim($this->getVal($row, $columnMap['parentesco'] ?? null));
            if (empty($parentesco)) {
                $parentesco = 'HIJO(A)';
            }

            // DNI de Apoderado (Opcional)
            $dniApoderadoRaw = $this->getVal($row, $columnMap['dni_apoderado'] ?? null);
            $padreFamiliaId = null;

            if (!empty($dniApoderadoRaw)) {
                $dniApoderado = preg_replace('/[^\d]/', '', $dniApoderadoRaw);
                if (strlen($dniApoderado) < 8) {
                    $dniApoderado = str_pad($dniApoderado, 8, '0', STR_PAD_LEFT);
                }

                if (isset($padresCache[$dniApoderado])) {
                    $padreFamiliaId = $padresCache[$dniApoderado]->id;
                } else {
                    $padreDb = PadreFamilia::where('empresa_id', $empresaId)
                        ->where('padre_dni', $dniApoderado)
                        ->first();
                    if ($padreDb) {
                        $padresCache[$dniApoderado] = $padreDb;
                        $padreFamiliaId = $padreDb->id;
                    } else {
                        $errores[] = "Fila #{$filaNumero} (Estudiante {$nombres} {$apePat}): El DNI de apoderado '{$dniApoderado}' no está registrado en la APAFA. El alumno fue registrado pendiente de asignación de apoderado.";
                    }
                }
            }

            // Buscar si ya existe el estudiante en esta institución
            $estudianteExistente = Estudiante::where('empresa_id', $empresaId)
                ->where('dni', $dni)
                ->first();

            if ($estudianteExistente) {
                $updateData = [
                    'nombres' => mb_strtoupper($nombres, 'UTF-8'),
                    'ape_pat' => mb_strtoupper($apePat, 'UTF-8'),
                    'ape_mat' => !empty($apeMat) ? mb_strtoupper($apeMat, 'UTF-8') : $estudianteExistente->ape_mat,
                    'nivel' => $nivel,
                    'grado' => $grado,
                    'seccion' => $seccion,
                    'turno' => $turno,
                ];

                if (!empty($padreFamiliaId)) {
                    $updateData['padre_familia_id'] = $padreFamiliaId;
                    $updateData['parentesco'] = mb_strtoupper($parentesco, 'UTF-8');
                }

                $estudianteExistente->update($updateData);
                $actualizados++;

                if (!empty($estudianteExistente->padre_familia_id) || !empty($padreFamiliaId)) {
                    $conApoderado++;
                } else {
                    $sinApoderado++;
                }
            } else {
                $estudiante = Estudiante::create([
                    'empresa_id' => $empresaId,
                    'padre_familia_id' => $padreFamiliaId,
                    'parentesco' => mb_strtoupper($parentesco, 'UTF-8'),
                    'dni' => $dni,
                    'nombres' => mb_strtoupper($nombres, 'UTF-8'),
                    'ape_pat' => mb_strtoupper($apePat, 'UTF-8'),
                    'ape_mat' => !empty($apeMat) ? mb_strtoupper($apeMat, 'UTF-8') : null,
                    'nivel' => $nivel,
                    'grado' => $grado,
                    'seccion' => $seccion,
                    'turno' => $turno,
                ]);

                $creados++;
                if (!empty($padreFamiliaId)) {
                    $conApoderado++;
                } else {
                    $sinApoderado++;
                }
            }
        }

        return [
            'total' => $creados + $actualizados,
            'creados' => $creados,
            'actualizados' => $actualizados,
            'con_apoderado' => $conApoderado,
            'sin_apoderado' => $sinApoderado,
            'errores' => $errores,
        ];
    }

    /**
     * Mapea encabezados a nombres de campos canónicos.
     */
    protected function mapHeaders(array $headerRow): array
    {
        $map = [];
        foreach ($headerRow as $index => $colName) {
            $normalized = $this->cleanHeader($colName);

            if (in_array($normalized, ['dni', 'documento', 'nro_documento', 'nro_doc', 'identificacion', 'dni_estudiante'])) {
                $map['dni'] = $index;
            } elseif (in_array($normalized, ['nombres', 'nombre', 'nombres_estudiante', 'nombres_del_estudiante'])) {
                $map['nombres'] = $index;
            } elseif (in_array($normalized, ['apellido_paterno', 'ape_pat', 'primer_apellido', 'paterno'])) {
                $map['ape_pat'] = $index;
            } elseif (in_array($normalized, ['apellido_materno', 'ape_mat', 'segundo_apellido', 'materno'])) {
                $map['ape_mat'] = $index;
            } elseif (in_array($normalized, ['apellidos', 'apellidos_completos', 'apellidos_estudiante'])) {
                $map['apellidos'] = $index;
            } elseif (in_array($normalized, ['nivel', 'nivel_educativo', 'modalidad'])) {
                $map['nivel'] = $index;
            } elseif (in_array($normalized, ['grado', 'ano', 'grado_escolar'])) {
                $map['grado'] = $index;
            } elseif (in_array($normalized, ['seccion', 'secc', 'aula'])) {
                $map['seccion'] = $index;
            } elseif (in_array($normalized, ['turno', 'horario'])) {
                $map['turno'] = $index;
            } elseif (in_array($normalized, ['dni_apoderado', 'dni_padre', 'dni_tutor', 'apoderado_dni', 'padre_dni'])) {
                $map['dni_apoderado'] = $index;
            } elseif (in_array($normalized, ['parentesco', 'vinculo', 'relacion', 'parentesco_apoderado'])) {
                $map['parentesco'] = $index;
            }
        }
        return $map;
    }

    protected function cleanHeader(string $str): string
    {
        $str = mb_strtolower(trim($str), 'UTF-8');
        // Eliminar tildes y caracteres especiales
        $str = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü', '.', '-', '/', ' '],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u', '', '_', '_', '_'],
            $str
        );
        return preg_replace('/_+/', '_', trim($str, '_'));
    }

    protected function getVal(array $row, ?int $index, string $default = ''): string
    {
        if ($index === null || !isset($row[$index])) {
            return $default;
        }
        $val = trim((string)$row[$index]);
        return $val !== '' ? $val : $default;
    }

    protected function normalizarNivel(string $raw): string
    {
        $rawLower = mb_strtolower(trim($raw), 'UTF-8');
        if (str_contains($rawLower, 'ini')) {
            return 'Inicial';
        }
        if (str_contains($rawLower, 'sec')) {
            return 'Secundaria';
        }
        return 'Primaria';
    }

    protected function normalizarGrado(string $raw): string
    {
        $raw = trim($raw);
        if (empty($raw)) return '1ro';

        // Si viene solo número ej: "1" -> "1ro"
        if (preg_match('/^([1-6])$/', $raw, $m)) {
            $suffixes = [1 => '1ro', 2 => '2do', 3 => '3ro', 4 => '4to', 5 => '5to', 6 => '6to'];
            return $suffixes[(int)$m[1]] ?? "{$raw}to";
        }

        // Si viene "3 Años"
        if (preg_match('/^[345]\s*(anos|años)?$/i', $raw)) {
            $num = preg_replace('/[^\d]/', '', $raw);
            return "{$num} Años";
        }

        return $raw;
    }

    protected function normalizarSeccion(string $raw): string
    {
        $raw = trim($raw);
        if (empty($raw)) return 'A';
        $upper = mb_strtoupper($raw, 'UTF-8');
        if (in_array($upper, ['UNICA', 'ÚNICA', 'U'])) {
            return 'Única';
        }
        return substr($upper, 0, 10);
    }

    protected function normalizarTurno(string $raw): string
    {
        $rawLower = mb_strtolower(trim($raw), 'UTF-8');
        if (str_contains($rawLower, 'tarde')) {
            return 'Tarde';
        }
        if (str_contains($rawLower, 'completo') || str_contains($rawLower, 'jornada')) {
            return 'Completo';
        }
        return 'Mañana';
    }

    /**
     * Parsea un archivo XLSX a un array de filas.
     */
    public function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        // Shared strings
        $sharedStrings = [];
        $ssContent = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssContent !== false) {
            $xml = @simplexml_load_string($ssContent);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string)$si->t;
                    } elseif (isset($si->r)) {
                        $txt = '';
                        foreach ($si->r as $r) {
                            $txt .= (string)$r->t;
                        }
                        $sharedStrings[] = $txt;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // Sheet 1
        $sheetContent = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetContent === false) {
            return [];
        }

        $xml = @simplexml_load_string($sheetContent);
        if (!$xml || !isset($xml->sheetData->row)) {
            return [];
        }

        $rows = [];
        foreach ($xml->sheetData->row as $r) {
            $rowValues = [];
            $maxCol = 0;

            foreach ($r->c as $c) {
                $attr = $c->attributes();
                $ref = isset($attr['r']) ? (string)$attr['r'] : '';
                $type = isset($attr['t']) ? (string)$attr['t'] : 'n';

                $val = '';
                if ($type === 's') {
                    $sIdx = isset($c->v) ? (int)$c->v : -1;
                    $val = $sharedStrings[$sIdx] ?? '';
                } elseif ($type === 'inlineStr' && isset($c->is->t)) {
                    $val = (string)$c->is->t;
                } else {
                    $val = isset($c->v) ? (string)$c->v : '';
                }

                // Obtener columna (ej: 'A', 'B', 'AA')
                if (!empty($ref) && preg_match('/^([A-Z]+)\d+$/i', $ref, $m)) {
                    $colIdx = $this->colLetterToIndex($m[1]);
                    $rowValues[$colIdx] = trim($val);
                    if ($colIdx > $maxCol) $maxCol = $colIdx;
                } else {
                    $rowValues[] = trim($val);
                    $maxCol = count($rowValues) - 1;
                }
            }

            // Normalizar a un array secuencial 0..$maxCol con strings vacíos en huecos
            $normalizedRow = [];
            for ($i = 0; $i <= $maxCol; $i++) {
                $normalizedRow[$i] = $rowValues[$i] ?? '';
            }

            // Ignorar filas totalmente vacías
            $hasContent = false;
            foreach ($normalizedRow as $cv) {
                if ($cv !== '') {
                    $hasContent = true;
                    break;
                }
            }

            if ($hasContent) {
                $rows[] = $normalizedRow;
            }
        }

        return $rows;
    }

    /**
     * Parsea un archivo CSV detectando delimitador y codificación.
     */
    public function parseCsv(string $filePath): array
    {
        $raw = file_get_contents($filePath);
        if ($raw === false || strlen(trim($raw)) === 0) {
            return [];
        }

        // Remover BOM UTF-8
        if (substr($raw, 0, 3) === "\xEF\xBB\xBF") {
            $raw = substr($raw, 3);
        }

        // Convertir ISO-8859-1 o Windows-1252 a UTF-8 si no es UTF-8 válido
        if (!mb_check_encoding($raw, 'UTF-8')) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'ISO-8859-1');
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($raw));
        if (empty($lines)) {
            return [];
        }

        // Detectar delimitador inspeccionando la primera línea
        $firstLine = $lines[0];
        $delimiters = [';' => 0, ',' => 0, "\t" => 0];
        foreach ($delimiters as $delim => &$count) {
            $count = count(str_getcsv($firstLine, $delim));
        }
        arsort($delimiters);
        $bestDelim = array_key_first($delimiters) ?: ',';

        $rows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') continue;
            $row = str_getcsv($line, $bestDelim);
            $rows[] = array_map('trim', $row);
        }

        return $rows;
    }

    protected function colLetterToIndex(string $col): int
    {
        $col = strtoupper($col);
        $len = strlen($col);
        $idx = 0;
        for ($i = 0; $i < $len; $i++) {
            $idx = $idx * 26 + (ord($col[$i]) - 64);
        }
        return $idx - 1;
    }

    protected function indexToColLetter(int $colIndex): string
    {
        $colIndex++;
        $letter = '';
        while ($colIndex > 0) {
            $rem = ($colIndex - 1) % 26;
            $letter = chr(65 + $rem) . $letter;
            $colIndex = intval(($colIndex - $rem) / 26);
        }
        return $letter;
    }

    /**
     * Crea un archivo XLSX válido en la ruta especificada.
     */
    protected function createXlsxFile(string $filePath, array $headers, array $rows): void
    {
        $zip = new ZipArchive();
        $zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
            '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
            '<Default Extension="xml" ContentType="application/xml"/>' .
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>' .
            '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' .
            '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStringItem+xml"/>' .
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>' .
            '</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>' .
            '</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
            '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>' .
            '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>' .
            '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' .
            '</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        // xl/workbook.xml
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">' .
            '<sheets><sheet name="Estudiantes" sheetId="1" r:id="rId1"/></sheets>' .
            '</workbook>';
        $zip->addFromString('xl/workbook.xml', $workbook);

        // xl/styles.xml (Estilo básico para encabezados con negrita y fondo)
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<fonts count="2">' .
            '<font><sz val="11"/><name val="Calibri"/></font>' .
            '<font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font>' .
            '</fonts>' .
            '<fills count="3">' .
            '<fill><patternFill patternType="none"/></fill>' .
            '<fill><patternFill patternType="gray125"/></fill>' .
            '<fill><patternFill patternType="solid"><fgColor rgb="FF4F46E5"/></patternFill></fill>' . // Indigo header
            '</fills>' .
            '<borders count="1"><border><left/><right/><top/><bottom/></border></borders>' .
            '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>' .
            '<cellXfs count="2">' .
            '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' . // Normal
            '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>' . // Header
            '</cellXfs>' .
            '</styleSheet>';
        $zip->addFromString('xl/styles.xml', $styles);

        // Shared strings
        $allData = array_merge([$headers], $rows);
        $sharedStrings = [];
        $stringLookup = [];

        $getSharedStringIndex = function($str) use (&$sharedStrings, &$stringLookup) {
            $str = (string)$str;
            if (isset($stringLookup[$str])) {
                return $stringLookup[$str];
            }
            $idx = count($sharedStrings);
            $sharedStrings[] = $str;
            $stringLookup[$str] = $idx;
            return $idx;
        };

        // xl/worksheets/sheet1.xml
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' .
            '<cols>';
        // Ancho de columnas predeterminado agradable
        for ($c = 1; $c <= count($headers); $c++) {
            $sheetXml .= '<col min="' . $c . '" max="' . $c . '" width="20" customWidth="1"/>';
        }
        $sheetXml .= '</cols><sheetData>';

        $rowNum = 1;
        foreach ($allData as $row) {
            $isHeader = ($rowNum === 1);
            $styleAttr = $isHeader ? ' s="1"' : '';
            $sheetXml .= '<row r="' . $rowNum . '">';
            $colNum = 0;
            foreach ($row as $cellVal) {
                $colLetter = $this->indexToColLetter($colNum);
                $cellRef = $colLetter . $rowNum;
                $sIdx = $getSharedStringIndex($cellVal);
                $sheetXml .= '<c r="' . $cellRef . '" t="s"' . $styleAttr . '><v>' . $sIdx . '</v></c>';
                $colNum++;
            }
            $sheetXml .= '</row>';
            $rowNum++;
        }
        $sheetXml .= '</sheetData></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        // xl/sharedStrings.xml
        $ssXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">';
        foreach ($sharedStrings as $s) {
            $ssXml .= '<si><t>' . htmlspecialchars($s, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</t></si>';
        }
        $ssXml .= '</sst>';
        $zip->addFromString('xl/sharedStrings.xml', $ssXml);

        $zip->close();
    }
}
