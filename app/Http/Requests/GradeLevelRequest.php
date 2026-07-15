<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GradeLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('grade_level')?->id;

        return [
            'name' => ['required', 'string', 'max:50'],
            'code' => [
                'required', 'string', 'max:10',
                Rule::unique('grade_levels', 'code')->ignore($id),
            ],
            'level_order' => ['required', 'integer', 'min:1', 'max:20'],
            'is_graduating' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_graduating' => $this->boolean('is_graduating')]);
    }
}
