<?php

namespace App\Http\Requests;

use App\Models\School;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('school')?->id;

        return [
            'school_id' => [
                'required', 'string', 'max:20',
                Rule::unique('schools', 'school_id')->ignore($id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'short_name' => ['nullable', 'string', 'max:255'],
            'previous_name' => ['nullable', 'string', 'max:255'],
            'mother_school_school_id' => ['nullable', 'string', 'max:20'],
            'source_school_year' => ['nullable', 'string', 'max:20'],
            'education_level' => ['required', Rule::in(array_keys(School::LEVELS))],
            'division' => ['nullable', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:255'],
            'municipality' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'legislative_district' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'school_head' => ['nullable', 'string', 'max:255'],
            'school_head_designation' => ['nullable', 'string', 'max:255'],
            'telephone_number' => ['nullable', 'string', 'max:255'],
            'fax_number' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'date_of_operation' => ['nullable', 'date'],
            'sub_classification' => ['nullable', 'string', 'max:255'],
            'curricular_class' => ['nullable', 'string', 'max:255'],
            'school_type' => ['nullable', 'string', 'max:255'],
            'class_organization' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
