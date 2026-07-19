<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherRegistrationRequest;
use App\Models\School;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view with the list of schools a teacher may join.
     */
    public function create(): View
    {
        return view('auth.register', [
            'schools' => School::active()->orderBy('name')->get(),
        ]);
    }

    /**
     * Handle an incoming registration request. New teachers land in a `pending`
     * state and cannot use the app until an administrator has reviewed their
     * school ID and approved them.
     */
    public function store(TeacherRegistrationRequest $request, AuditLogger $audit): RedirectResponse
    {
        $data = $request->validated();

        /*
         * Stored on the private disk under a random name. Laravel's store()
         * derives the extension from the verified MIME type rather than the
         * client-supplied filename, so nothing user-controlled reaches the
         * path — no traversal, no ".php" smuggled through.
         */
        $documentPath = $request->file('school_id_document')->store('school-ids', 'local');

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'contact_number' => $data['contact_number'],
            'school_id' => $data['school_id'],
            'school_id_number' => $data['school_id_number'],
            'school_id_document_path' => $documentPath,
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_TEACHER,
            'status' => User::STATUS_PENDING,
        ]);

        $audit->log('registration_submitted', $user,
            "Teacher registration submitted for {$user->email} with a school ID for review",
            null,
            ['school_id' => $user->school_id, 'school_id_number' => $user->school_id_number],
            $user->id,
        );

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('account.pending');
    }
}
