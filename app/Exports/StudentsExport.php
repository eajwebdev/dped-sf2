<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StudentsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private readonly ?string $search = null, private readonly ?string $status = null) {}

    public function query()
    {
        return Student::query()
            ->when($this->search, fn ($q) => $q->where(fn ($w) => $w
                ->where('lrn', 'like', "%{$this->search}%")
                ->orWhere('first_name', 'like', "%{$this->search}%")
                ->orWhere('last_name', 'like', "%{$this->search}%")))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->orderBy('last_name')->orderBy('first_name');
    }

    public function headings(): array
    {
        return ['lrn', 'first_name', 'middle_name', 'last_name', 'suffix', 'gender', 'birthdate', 'address', 'guardian_name', 'guardian_contact', 'status'];
    }

    public function map($student): array
    {
        return [
            $student->lrn,
            $student->first_name,
            $student->middle_name,
            $student->last_name,
            $student->suffix,
            $student->gender,
            $student->birthdate?->toDateString(),
            $student->address,
            $student->guardian_name,
            $student->guardian_contact,
            $student->status,
        ];
    }
}
