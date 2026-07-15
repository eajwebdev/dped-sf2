<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $teacher = $this->route('teacher');
        $id = $teacher?->id;
        $userId = $teacher?->user_id;

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', Rule::in(['Male', 'Female'])],
            'employee_no' => ['nullable', 'string', 'max:50', Rule::unique('teachers', 'employee_no')->ignore($id)],
            'email' => ['nullable', 'email', 'max:150'],
            'contact' => ['nullable', 'string', 'max:30'],
            'is_active' => ['boolean'],

            // Optional linked login account.
            'create_account' => ['boolean'],
            'account_email' => [
                'nullable',
                Rule::requiredIf(fn () => $this->boolean('create_account')),
                'email', 'max:150',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'account_password' => [
                'nullable',
                Rule::requiredIf(fn () => $this->boolean('create_account') && ! $userId),
                'string', 'min:8',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'create_account' => $this->boolean('create_account'),
        ]);
    }
}
