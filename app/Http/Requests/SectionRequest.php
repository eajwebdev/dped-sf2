<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('section')?->id;

        return [
            'school_year_id' => ['required', 'exists:school_years,id'],
            'grade_level_id' => ['required', 'exists:grade_levels,id'],
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('sections', 'name')
                    ->where('school_year_id', $this->input('school_year_id'))
                    ->where('grade_level_id', $this->input('grade_level_id'))
                    ->ignore($id),
            ],
            'adviser_id' => ['nullable', 'exists:teachers,id'],
            'room' => ['nullable', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A section with this name already exists for the selected grade and school year.',
        ];
    }
}
