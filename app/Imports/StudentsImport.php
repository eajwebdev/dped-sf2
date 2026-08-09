<?php

namespace App\Imports;

use App\Models\Section;
use App\Models\Student;
use App\Services\EnrollmentService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Imports learners from a spreadsheet.
 *
 * Required headings: first_name, last_name, gender.
 * Optional starter headings: lrn, middle_name, suffix, birthdate, address,
 * guardian_name, guardian_contact, plus the fuller SF1 profile fields.
 */
class StudentsImport implements SkipsEmptyRows, ToModel, WithHeadingRow, WithValidation
{
    public int $imported = 0;

    public int $skipped = 0;

    public int $enrolled = 0;

    public int $enrollmentSkipped = 0;

    private const FIELD_ALIASES = [
        'lrn' => ['lrn', 'learner_reference_number'],
        'first_name' => ['first_name', 'firstname', 'given_name', 'given_names'],
        'middle_name' => ['middle_name', 'middlename', 'middle_initial'],
        'last_name' => ['last_name', 'lastname', 'surname', 'family_name'],
        'suffix' => ['suffix', 'extension_name', 'name_extension'],
        'gender' => ['gender', 'sex'],
        'birthdate' => ['birthdate', 'birth_date', 'date_of_birth', 'dob'],
        'birth_place' => ['birth_place', 'birthplace', 'place_of_birth'],
        'mother_tongue' => ['mother_tongue', 'mother_tongue_language'],
        'ethnic_group' => ['ethnic_group', 'ethnicity', 'ip', 'indigenous_people'],
        'religion' => ['religion'],
        'address' => ['address', 'home_address'],
        'address_street' => ['address_street', 'house_street', 'house_no_street', 'house_street_sitio_purok', 'house_no_street_sitio_purok'],
        'address_barangay' => ['address_barangay', 'barangay', 'brgy'],
        'address_municipality' => ['address_municipality', 'municipality', 'city', 'municipality_city'],
        'address_province' => ['address_province', 'province'],
        'father_name' => ['father_name', 'fathers_name'],
        'mother_name' => ['mother_name', 'mothers_name', 'mother_maiden_name'],
        'guardian_name' => ['guardian_name', 'parent_guardian', 'parent_or_guardian'],
        'guardian_relationship' => ['guardian_relationship', 'relationship'],
        'guardian_contact' => ['guardian_contact', 'contact_number', 'contact_no', 'parent_contact', 'guardian_contact_number'],
    ];

    public function __construct(
        private readonly ?Section $section = null,
        private readonly ?EnrollmentService $enrollment = null,
    ) {}

    public static function templateHeadings(): array
    {
        return [
            'lrn',
            'first_name',
            'middle_name',
            'last_name',
            'suffix',
            'gender',
            'birthdate',
            'birth_place',
            'mother_tongue',
            'ethnic_group',
            'religion',
            'address',
            'address_street',
            'address_barangay',
            'address_municipality',
            'address_province',
            'father_name',
            'mother_name',
            'guardian_name',
            'guardian_relationship',
            'guardian_contact',
        ];
    }

    public static function templateSampleRow(): array
    {
        return ['', 'Juan', 'Reyes', 'Dela Cruz', '', 'Male', '2013-05-01', '', '', '', '', 'Sample Address', '', '', '', '', '', '', 'Maria Dela Cruz', 'Parent', '09171234567'];
    }

    /**
     * Normalize common heading aliases before validation runs.
     */
    public function prepareForValidation(array $data, int $index): array
    {
        foreach (self::FIELD_ALIASES as $field => $aliases) {
            $data[$field] = $this->text($this->firstValue($data, $aliases));
        }

        $this->fillNamesFromSingleNameColumn($data);

        $data['lrn'] = $this->normalizeLrn($data['lrn']);
        $data['gender'] = $this->normalizeGender($data['gender']);

        return $data;
    }

    public function model(array $row): ?Student
    {
        $existing = ! empty($row['lrn'])
            ? Student::where('lrn', $row['lrn'])->first()
            : null;

        if ($existing) {
            if ($this->section && $this->enrollment) {
                $this->enroll($existing);

                return null;
            }

            $this->skipped++;

            return null;
        }

        $student = Student::create([
            'lrn' => $row['lrn'],
            'first_name' => $row['first_name'],
            'middle_name' => $row['middle_name'] ?? null,
            'last_name' => $row['last_name'],
            'suffix' => $row['suffix'] ?? null,
            'gender' => $row['gender'],
            'birthdate' => ! empty($row['birthdate']) ? $this->parseDate($row['birthdate']) : null,
            'address' => $row['address'] ?? null,
            'birth_place' => $row['birth_place'] ?? null,
            'mother_tongue' => $row['mother_tongue'] ?? null,
            'ethnic_group' => $row['ethnic_group'] ?? null,
            'religion' => $row['religion'] ?? null,
            'address_street' => $row['address_street'] ?? null,
            'address_barangay' => $row['address_barangay'] ?? null,
            'address_municipality' => $row['address_municipality'] ?? null,
            'address_province' => $row['address_province'] ?? null,
            'father_name' => $row['father_name'] ?? null,
            'mother_name' => $row['mother_name'] ?? null,
            'guardian_name' => $row['guardian_name'] ?? null,
            'guardian_relationship' => $row['guardian_relationship'] ?? null,
            'guardian_contact' => $row['guardian_contact'] ?? null,
            'status' => 'active',
            'qr_token' => (string) Str::uuid(),
        ]);

        $this->imported++;
        $this->enroll($student);

        return null;
    }

    public function rules(): array
    {
        return [
            'lrn' => ['nullable', 'digits:12'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'birthdate' => ['nullable'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'mother_tongue' => ['nullable', 'string', 'max:255'],
            'ethnic_group' => ['nullable', 'string', 'max:255'],
            'religion' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'address_barangay' => ['nullable', 'string', 'max:255'],
            'address_municipality' => ['nullable', 'string', 'max:255'],
            'address_province' => ['nullable', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_relationship' => ['nullable', 'string', 'max:50'],
            'guardian_contact' => ['nullable', 'string', 'max:30'],
        ];
    }

    private function firstValue(array $row, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && ! blank($row[$key])) {
                return $row[$key];
            }
        }

        return null;
    }

    private function enroll(Student $student): void
    {
        if (! $this->section || ! $this->enrollment) {
            return;
        }

        try {
            $this->enrollment->enroll($student, $this->section);
            $this->enrolled++;
        } catch (ValidationException) {
            $this->enrollmentSkipped++;
        }
    }

    private function fillNamesFromSingleNameColumn(array &$data): void
    {
        if (! blank($data['first_name']) && ! blank($data['last_name'])) {
            return;
        }

        $name = $this->text($this->firstValue($data, ['name', 'full_name', 'learner_name']));

        if (! $name) {
            return;
        }

        if (str_contains($name, ',')) {
            [$last, $rest] = array_map('trim', explode(',', $name, 2));
            $parts = preg_split('/\s+/', $rest) ?: [];

            $data['last_name'] = $data['last_name'] ?: $last;
            $data['first_name'] = $data['first_name'] ?: array_shift($parts);
            $data['middle_name'] = $data['middle_name'] ?: ($parts ? implode(' ', $parts) : null);

            return;
        }

        $parts = preg_split('/\s+/', $name) ?: [];

        if (count($parts) >= 2) {
            $data['first_name'] = $data['first_name'] ?: array_shift($parts);
            $data['last_name'] = $data['last_name'] ?: array_pop($parts);
            $data['middle_name'] = $data['middle_name'] ?: ($parts ? implode(' ', $parts) : null);
        }
    }

    private function normalizeLrn(mixed $value): ?string
    {
        $value = $this->text($value);

        if (! $value) {
            return null;
        }

        if (is_numeric($value)) {
            $value = number_format((float) $value, 0, '.', '');
        }

        return preg_replace('/\D+/', '', $value) ?: null;
    }

    private function normalizeGender(mixed $value): ?string
    {
        $value = mb_strtolower((string) $this->text($value));

        return match ($value) {
            'm', 'male' => 'Male',
            'f', 'female' => 'Female',
            default => null,
        };
    }

    private function text(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_float($value) && floor($value) === $value) {
            $value = number_format($value, 0, '.', '');
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
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
