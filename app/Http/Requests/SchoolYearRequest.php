<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SchoolYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // authorization handled by the controller policy
    }

    public function rules(): array
    {
        $id = $this->route('school_year')?->id;

        return [
            'name' => [
                'required', 'string', 'max:20',
                Rule::unique('school_years', 'name')->ignore($id),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ];
    }
}
