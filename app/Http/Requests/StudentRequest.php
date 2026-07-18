<?php

namespace App\Http\Requests;

use App\Models\Student;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('student')?->id;
        $schoolId = $this->user()?->school_id;

        return [
            'lrn' => [
                'required', 'digits:12',
                Rule::unique('students', 'lrn')
                    ->ignore($id)
                    ->where(fn ($q) => $q
                        ->when($schoolId, fn ($qq) => $qq->where('school_id', $schoolId))
                        ->whereNull('deleted_at')),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'gender' => ['required', Rule::in(['Male', 'Female'])],
            'birthdate' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:500'],

            // SF1 (School Register) learner profile.
            'birth_place' => ['nullable', 'string', 'max:255'],
            'mother_tongue' => ['nullable', 'string', 'max:255'],
            'ethnic_group' => ['nullable', 'string', 'max:255'],
            'religion' => ['nullable', 'string', 'max:255'],
            'address_street' => ['nullable', 'string', 'max:255'],
            'address_barangay' => ['nullable', 'string', 'max:255'],
            'address_municipality' => ['nullable', 'string', 'max:255'],
            'address_province' => ['nullable', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],

            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_relationship' => ['nullable', 'string', 'max:50'],
            'guardian_contact' => ['nullable', 'string', 'max:30'],
            'status' => ['required', Rule::in(['active', 'transferred', 'dropped', 'graduated', 'inactive'])],
            'photo' => ['nullable', 'image', 'max:2048'], // 2 MB
        ];
    }

    /**
     * Name the existing learner instead of a bare "already taken", and catch the
     * same person re-added under a different LRN (a typo'd digit slips past the
     * unique rule and silently creates a duplicate roster entry).
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $id = $this->route('student')?->id;

            if ($v->errors()->has('lrn') && $this->filled('lrn')) {
                $existing = Student::withoutGlobalScopes()
                    ->where('lrn', $this->input('lrn'))
                    ->whereNull('deleted_at')
                    ->first();

                if ($existing) {
                    $v->errors()->forget('lrn');
                    $v->errors()->add('lrn', "LRN {$this->input('lrn')} already belongs to {$existing->full_name} in this school.");
                }

                return;
            }

            if (! $this->filled(['first_name', 'last_name'])) {
                return;
            }

            $twin = Student::withoutGlobalScopes()
                ->when($id, fn ($q) => $q->whereKeyNot($id))
                ->when($this->user()?->school_id, fn ($q, $s) => $q->where('school_id', $s))
                ->whereNull('deleted_at')
                ->whereRaw('LOWER(first_name) = ?', [mb_strtolower(trim($this->input('first_name')))])
                ->whereRaw('LOWER(last_name) = ?', [mb_strtolower(trim($this->input('last_name')))])
                ->when($this->filled('birthdate'), fn ($q) => $q->whereDate('birthdate', $this->date('birthdate')))
                ->first();

            if ($twin) {
                $v->errors()->add('first_name',
                    "{$twin->full_name} is already in this school (LRN {$twin->lrn}). Use a different name, or edit the existing learner.");
            }
        });
    }
}
