<?php

namespace App\Services;

use App\Models\School;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

class SchoolMasterlistImportService
{
    /**
     * Columns written to the FOR IMPORT sheet. They intentionally match the
     * schools table fillable fields so the sheet is database-ready.
     */
    public const IMPORT_HEADERS = [
        'school_id',
        'name',
        'short_name',
        'previous_name',
        'mother_school_school_id',
        'source_school_year',
        'education_level',
        'address',
        'region',
        'province',
        'municipality',
        'district',
        'legislative_district',
        'division',
        'school_head',
        'school_head_designation',
        'telephone_number',
        'fax_number',
        'email',
        'date_of_operation',
        'sub_classification',
        'curricular_class',
        'school_type',
        'class_organization',
        'is_active',
    ];

    /**
     * Import the DepEd masterlist workbook. Preferred input is the normalized
     * `FOR IMPORT` sheet, but the raw `Worksheet` report sheet is still accepted.
     *
     * @return array{created:int, updated:int, skipped:int, total:int, sheet:string, source_school_year:?string}
     */
    public function import(string $path, string $sheetName = 'FOR IMPORT'): array
    {
        $spreadsheet = $this->loadSpreadsheet($path, true);
        $sheet = $spreadsheet->getSheetByName($sheetName);

        if (! $sheet) {
            $spreadsheet->disconnectWorksheets();

            throw new RuntimeException("Sheet [{$sheetName}] was not found in the school masterlist.");
        }

        $extracted = $this->looksLikeImportSheet($sheet)
            ? $this->extractImportRows($sheet)
            : $this->extractWorksheetRows($sheet);

        $result = $this->persistRows($extracted['rows'], $extracted['skipped'], $sheetName, $extracted['source_school_year']);

        $spreadsheet->disconnectWorksheets();

        return $result;
    }

    /**
     * Rebuild the FOR IMPORT sheet from the raw Worksheet data and save the
     * workbook. This makes the uploaded file itself usable as the import source.
     *
     * @return array{rows:int, skipped:int, sheet:string, source_sheet:string, source_school_year:?string}
     */
    public function populateImportSheet(string $path, string $sourceSheet = 'Worksheet', string $importSheet = 'FOR IMPORT'): array
    {
        $spreadsheet = $this->loadSpreadsheet($path, false);
        $sheet = $spreadsheet->getSheetByName($sourceSheet);

        if (! $sheet) {
            $spreadsheet->disconnectWorksheets();

            throw new RuntimeException("Sheet [{$sourceSheet}] was not found in the school masterlist.");
        }

        $extracted = $this->extractWorksheetRows($sheet);

        if ($existing = $spreadsheet->getSheetByName($importSheet)) {
            $index = $spreadsheet->getIndex($existing);
            $spreadsheet->removeSheetByIndex($index);
        } else {
            $index = $spreadsheet->getSheetCount();
        }

        $import = new Worksheet($spreadsheet, $importSheet);
        $spreadsheet->addSheet($import, $index);
        $import->fromArray(self::IMPORT_HEADERS, null, 'A1');

        $rowNumber = 2;
        foreach ($extracted['rows'] as $row) {
            $import->fromArray(
                array_map(fn (string $header) => $row[$header] ?? null, self::IMPORT_HEADERS),
                null,
                'A'.$rowNumber
            );
            $rowNumber++;
        }

        $lastColumn = Coordinate::stringFromColumnIndex(count(self::IMPORT_HEADERS));
        $import->freezePane('A2');
        $import->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
        $import->setAutoFilter("A1:{$lastColumn}1");

        for ($column = 1; $column <= count(self::IMPORT_HEADERS); $column++) {
            $import->getColumnDimension(Coordinate::stringFromColumnIndex($column))->setAutoSize(true);
        }

        $writer = IOFactory::createWriter($spreadsheet, ucfirst(pathinfo($path, PATHINFO_EXTENSION)));
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();

        return [
            'rows' => count($extracted['rows']),
            'skipped' => $extracted['skipped'],
            'sheet' => $importSheet,
            'source_sheet' => $sourceSheet,
            'source_school_year' => $extracted['source_school_year'],
        ];
    }

    private function loadSpreadsheet(string $path, bool $readDataOnly)
    {
        if (! is_file($path)) {
            throw new RuntimeException("School masterlist file not found: {$path}");
        }

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly($readDataOnly);

        return $reader->load($path);
    }

    /**
     * @return array{rows:array<int,array<string,mixed>>, skipped:int, source_school_year:?string}
     */
    private function extractWorksheetRows(Worksheet $sheet): array
    {
        $sourceSchoolYear = $this->sourceSchoolYear((string) $sheet->getCell('A4')->getValue());
        $highestRow = $sheet->getHighestDataRow();
        $highestColumnIndex = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        $currentDistrict = null;
        $rows = [];
        $skipped = 0;

        for ($rowNumber = 10; $rowNumber <= $highestRow; $rowNumber++) {
            $row = $sheet->rangeToArray(
                'A'.$rowNumber.':'.Coordinate::stringFromColumnIndex($highestColumnIndex).$rowNumber,
                null,
                true,
                false
            )[0];

            $firstCell = $this->cleanText($row[0] ?? null);

            if ($firstCell && str_starts_with(strtoupper($firstCell), 'DISTRICT:')) {
                $currentDistrict = trim(substr($firstCell, strlen('DISTRICT:'))) ?: null;
                $skipped++;

                continue;
            }

            if (! $this->cleanSchoolId($row[1] ?? null) || ! $this->cleanText($row[2] ?? null)) {
                $skipped++;

                continue;
            }

            $curricularClass = $this->cleanText($row[19] ?? null);

            $rows[] = [
                'school_id' => $this->cleanSchoolId($row[1] ?? null),
                'name' => $this->cleanText($row[2] ?? null),
                'short_name' => $this->cleanText($row[3] ?? null),
                'previous_name' => $this->cleanText($row[4] ?? null),
                'mother_school_school_id' => $this->cleanSchoolId($row[5] ?? null),
                'source_school_year' => $sourceSchoolYear,
                'education_level' => $this->educationLevelFrom($curricularClass, $row[2] ?? null),
                'address' => $this->cleanText($row[6] ?? null),
                'region' => $this->cleanText($row[7] ?? null),
                'province' => $this->cleanText($row[8] ?? null),
                'municipality' => $this->cleanText($row[9] ?? null),
                'district' => $currentDistrict,
                'legislative_district' => $this->cleanText($row[10] ?? null),
                'division' => $this->cleanText($row[11] ?? null),
                'school_head' => $this->cleanText($row[12] ?? null),
                'school_head_designation' => $this->cleanText($row[13] ?? null),
                'telephone_number' => $this->cleanText($row[14] ?? null),
                'fax_number' => $this->cleanText($row[15] ?? null),
                'email' => $this->cleanText($row[16] ?? null),
                'date_of_operation' => $this->cleanDate($row[17] ?? null),
                'sub_classification' => $this->cleanText($row[18] ?? null),
                'curricular_class' => $curricularClass,
                'school_type' => $this->cleanText($row[20] ?? null),
                'class_organization' => $this->cleanText($row[21] ?? null),
                'is_active' => true,
            ];
        }

        return ['rows' => $rows, 'skipped' => $skipped, 'source_school_year' => $sourceSchoolYear];
    }

    /**
     * @return array{rows:array<int,array<string,mixed>>, skipped:int, source_school_year:?string}
     */
    private function extractImportRows(Worksheet $sheet): array
    {
        $headers = $this->headers($sheet);
        $highestRow = $sheet->getHighestDataRow();
        $rows = [];
        $skipped = 0;

        for ($rowNumber = 2; $rowNumber <= $highestRow; $rowNumber++) {
            $row = [];

            foreach ($headers as $index => $header) {
                if (! in_array($header, self::IMPORT_HEADERS, true)) {
                    continue;
                }

                $column = Coordinate::stringFromColumnIndex($index + 1);
                $row[$header] = $sheet->getCell($column.$rowNumber)->getValue();
            }

            if (! $this->cleanSchoolId($row['school_id'] ?? null) || ! $this->cleanText($row['name'] ?? null)) {
                $skipped++;

                continue;
            }

            $curricularClass = $this->cleanText($row['curricular_class'] ?? null);
            $educationLevel = $this->cleanText($row['education_level'] ?? null);

            if (! in_array($educationLevel, array_keys(School::LEVELS), true)) {
                $educationLevel = $this->educationLevelFrom($curricularClass, $row['name'] ?? null);
            }

            $rows[] = [
                'school_id' => $this->cleanSchoolId($row['school_id'] ?? null),
                'name' => $this->cleanText($row['name'] ?? null),
                'short_name' => $this->cleanText($row['short_name'] ?? null),
                'previous_name' => $this->cleanText($row['previous_name'] ?? null),
                'mother_school_school_id' => $this->cleanSchoolId($row['mother_school_school_id'] ?? null),
                'source_school_year' => $this->cleanText($row['source_school_year'] ?? null),
                'education_level' => $educationLevel,
                'address' => $this->cleanText($row['address'] ?? null),
                'region' => $this->cleanText($row['region'] ?? null),
                'province' => $this->cleanText($row['province'] ?? null),
                'municipality' => $this->cleanText($row['municipality'] ?? null),
                'district' => $this->cleanText($row['district'] ?? null),
                'legislative_district' => $this->cleanText($row['legislative_district'] ?? null),
                'division' => $this->cleanText($row['division'] ?? null),
                'school_head' => $this->cleanText($row['school_head'] ?? null),
                'school_head_designation' => $this->cleanText($row['school_head_designation'] ?? null),
                'telephone_number' => $this->cleanText($row['telephone_number'] ?? null),
                'fax_number' => $this->cleanText($row['fax_number'] ?? null),
                'email' => $this->cleanText($row['email'] ?? null),
                'date_of_operation' => $this->cleanDate($row['date_of_operation'] ?? null),
                'sub_classification' => $this->cleanText($row['sub_classification'] ?? null),
                'curricular_class' => $curricularClass,
                'school_type' => $this->cleanText($row['school_type'] ?? null),
                'class_organization' => $this->cleanText($row['class_organization'] ?? null),
                'is_active' => $this->cleanBoolean($row['is_active'] ?? true),
            ];
        }

        return [
            'rows' => $rows,
            'skipped' => $skipped,
            'source_school_year' => $rows[0]['source_school_year'] ?? null,
        ];
    }

    /**
     * @param  array<int,array<string,mixed>>  $rows
     * @return array{created:int, updated:int, skipped:int, total:int, sheet:string, source_school_year:?string}
     */
    private function persistRows(array $rows, int $skipped, string $sheetName, ?string $sourceSchoolYear): array
    {
        if ($rows === []) {
            return [
                'created' => 0,
                'updated' => 0,
                'skipped' => $skipped,
                'total' => 0,
                'sheet' => $sheetName,
                'source_school_year' => $sourceSchoolYear,
            ];
        }

        $now = now();
        $rows = array_map(function (array $row) use ($now) {
            return $row + [
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $rows);

        $existingSchoolIds = School::withTrashed()
            ->pluck('school_id')
            ->mapWithKeys(fn ($schoolId) => [(string) $schoolId => true]);

        $created = 0;
        foreach ($rows as $row) {
            if (! isset($existingSchoolIds[$row['school_id']])) {
                $created++;
            }
        }

        $updateColumns = array_values(array_diff(array_keys($rows[0]), ['school_id', 'created_at']));
        $updateColumns[] = 'deleted_at';

        DB::transaction(function () use ($rows, $updateColumns): void {
            foreach (array_chunk($rows, 500) as $chunk) {
                $chunk = array_map(function (array $row) {
                    $row['deleted_at'] = null;

                    return $row;
                }, $chunk);

                School::withTrashed()->upsert($chunk, ['school_id'], $updateColumns);
            }
        });

        return [
            'created' => $created,
            'updated' => count($rows) - $created,
            'skipped' => $skipped,
            'total' => count($rows),
            'sheet' => $sheetName,
            'source_school_year' => $sourceSchoolYear,
        ];
    }

    private function looksLikeImportSheet(Worksheet $sheet): bool
    {
        $headers = $this->headers($sheet);

        return in_array('school_id', $headers, true) && in_array('name', $headers, true);
    }

    /**
     * @return array<int,string>
     */
    private function headers(Worksheet $sheet): array
    {
        $highestColumnIndex = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());
        $headers = [];

        for ($column = 1; $column <= $highestColumnIndex; $column++) {
            $headers[] = $this->normalizeHeader($sheet->getCell(Coordinate::stringFromColumnIndex($column).'1')->getValue());
        }

        return $headers;
    }

    private function normalizeHeader(mixed $value): string
    {
        $header = strtolower((string) $this->cleanText($value));
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);

        return trim((string) $header, '_');
    }

    private function sourceSchoolYear(string $value): ?string
    {
        return preg_match('/(\d{4})\s*-\s*(\d{4})/', $value, $matches)
            ? $matches[1].' - '.$matches[2]
            : null;
    }

    private function cleanText(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = preg_replace('/\s+/', ' ', trim((string) $value));

        if ($text === '') {
            return null;
        }

        return in_array(strtoupper($text), ['NONE', 'N/A', 'NA', '-'], true) ? null : $text;
    }

    private function cleanSchoolId(mixed $value): ?string
    {
        $text = $this->cleanText($value);

        if (! $text) {
            return null;
        }

        return preg_replace('/\.0$/', '', $text);
    }

    private function cleanBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $text = strtoupper((string) $this->cleanText($value));

        return ! in_array($text, ['0', 'FALSE', 'NO', 'N'], true);
    }

    private function cleanDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        $text = $this->cleanText($value);

        if (! $text) {
            return null;
        }

        try {
            return Carbon::parse($text)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function educationLevelFrom(?string $curricularClass, mixed $schoolName): ?string
    {
        $source = strtoupper(trim(($curricularClass ?? '').' '.($schoolName ?? '')));

        if (str_contains($source, 'ELEMENTARY')) {
            return School::LEVEL_ES;
        }

        if (str_contains($source, 'SENIOR HIGH') || preg_match('/\bSHS\b/', $source)) {
            return School::LEVEL_SHS;
        }

        if (str_contains($source, 'SECONDARY') || str_contains($source, 'HIGH SCHOOL') || preg_match('/\bNHS\b/', $source)) {
            return School::LEVEL_JHS;
        }

        return null;
    }
}
