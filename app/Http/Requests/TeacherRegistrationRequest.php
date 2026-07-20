<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rules\File;

/**
 * Self-registration is the app's only unauthenticated write path, so the rules
 * here are the front door's lock. The school ID upload in particular is an
 * untrusted file from an anonymous visitor.
 */
class TeacherRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Applicants pick who they are; anything else is coerced to teacher
            // in the controller. Only these two roles may ever self-register —
            // an admin is never provisioned through the public front door.
            'role' => ['nullable', 'in:'.User::ROLE_TEACHER.','.User::ROLE_SUPERVISOR],

            'name' => ['required', 'string', 'max:255'],
            // RFC validation only. A `dns` check would do a live MX lookup on
            // every signup: it rejects legitimate school domains that publish
            // no MX record, and turns a DNS outage into a broken signup page.
            'email' => ['required', 'string', 'lowercase', 'email:rfc', 'max:255', 'unique:'.User::class],
            'contact_number' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
            'school_id' => ['required', 'integer', 'exists:schools,id'],

            // The employee/teacher number printed on the ID, so the admin can
            // check it against the school's own records.
            'school_id_number' => ['required', 'string', 'max:60', 'regex:/^[A-Za-z0-9\-\/ ]+$/'],

            /*
             * Validated by real content, not by the name the browser sent:
             * File::image() checks the decoded MIME type, so "payload.php"
             * renamed to "id.jpg" is rejected. SVG is excluded deliberately —
             * it is XML and can carry script. HEIC is excluded because GD
             * cannot decode it, so the dimensions rule below would reject every
             * such upload with a misleading "too small" message; iOS converts
             * HEIC to JPEG when posting through a file input anyway.
             */
            'school_id_document' => [
                'required',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->min('20kb')
                    ->max('8mb'),
                'dimensions:min_width=200,min_height=200,max_width=8000,max_height=8000',
            ],

            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'school_id_document.required' => 'Please upload a photo of your school ID so we can verify you work at this school.',
            'school_id_document.dimensions' => 'That image is too small to read. Please upload a clearer photo of your ID.',
            'school_id_number.regex' => 'The ID number may only contain letters, numbers, spaces, dashes and slashes.',
            'contact_number.regex' => 'Please enter a valid contact number.',
        ];
    }
}
