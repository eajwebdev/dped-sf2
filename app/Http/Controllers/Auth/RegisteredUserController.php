<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
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
     * state and cannot use the app until an administrator approves them.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'contact_number' => ['required', 'string', 'max:30'],
            'school_id' => ['required', 'exists:schools,id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'contact_number' => $data['contact_number'],
            'school_id' => $data['school_id'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_TEACHER,
            'status' => User::STATUS_PENDING,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('account.pending');
    }
}
