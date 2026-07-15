<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Imports learners from a spreadsheet. Expected headings (row 1):
 * lrn, first_name, middle_name, last_name, suffix, gender, birthdate,
 * address, guardian_name, guardian_contact
 */
class StudentsImport implements SkipsEmptyRows, ToModel, WithHeadingRow, WithValidation
{
    public int $imported = 0;

    public function model(array $row): ?Student
    {
        // Skip rows whose LRN already exists (idempotent re-imports).
        if (empty($row['lrn']) || Student::where('lrn', $row['lrn'])->exists()) {
            return null;
        }

        $this->imported++;

        return new Student([
            'lrn' => (string) $row['lrn'],
            'first_name' => $row['first_name'] ?? '',
            'middle_name' => $row['middle_name'] ?? null,
            'last_name' => $row['last_name'] ?? '',
            'suffix' => $row['suffix'] ?? null,
            'gender' => in_array($row['gender'] ?? '', ['Male', 'Female'], true) ? $row['gender'] : 'Male',
            'birthdate' => ! empty($row['birthdate']) ? $this->parseDate($row['birthdate']) : null,
            'address' => $row['address'] ?? null,
            'guardian_name' => $row['guardian_name'] ?? null,
            'guardian_contact' => $row['guardian_contact'] ?? null,
            'status' => 'active',
            'qr_token' => (string) Str::uuid(),
        ]);
    }

    public function rules(): array
    {
        return [
            'lrn' => ['required'],
            'first_name' => ['required'],
            'last_name' => ['required'],
        ];
    }

    private function parseDate(mixed $value): ?string
    {
        try {
            // Excel serial date or a normal date string.
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            }

            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
