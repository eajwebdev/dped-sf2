<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** Pending teacher self-registrations awaiting approval. */
    public function index()
    {
        $admin = auth()->user();

        $pending = User::with('school')
            ->where('role', User::ROLE_TEACHER)
            ->where('status', User::STATUS_PENDING)
            // A school's own admin reviews only their applicants; a platform
            // admin (no school of their own) sees every school's queue.
            ->when($admin->school_id, fn ($q) => $q->where('school_id', $admin->school_id))
            ->latest()
            ->paginate(20);

        return view('admin.registrations.index', compact('pending'));
    }

    /**
     * Approve a registration: start the free trial and provision the linked
     * Teacher record so the account can immediately take attendance.
     */
    public function approve(User $user): RedirectResponse
    {
        abort_unless($user->isTeacher(), 404);
        $this->authorizeSameSchool($user);

        DB::transaction(function () use ($user) {
            $user->forceFill([
                'status' => User::STATUS_APPROVED,
                'trial_ends_at' => Carbon::now()->addDays(User::TRIAL_DAYS),
                'approved_at' => Carbon::now(),
                'approved_by' => auth()->id(),
                // Approving is the act of confirming the ID belongs to this school.
                'school_id_verified_at' => Carbon::now(),
                'school_id_verified_by' => auth()->id(),
            ])->save();

            if (! $user->teacher()->exists()) {
                [$first, $last] = $this->splitName($user->name);
                Teacher::create([
                    'school_id' => $user->school_id,
                    'user_id' => $user->id,
                    'first_name' => $first,
                    'last_name' => $last,
                    'email' => $user->email,
                    'contact' => $user->contact_number,
                    'is_active' => true,
                ]);
            }
        });

        $this->audit->log('approved', $user,
            "Approved teacher registration for {$user->email}; school ID {$user->school_id_number} verified");

        return back()->with('success', "{$user->name} approved — ".User::TRIAL_DAYS."-day trial started.");
    }

    public function reject(User $user): RedirectResponse
    {
        abort_unless($user->isTeacher(), 404);
        $this->authorizeSameSchool($user);

        $user->forceFill(['status' => User::STATUS_REJECTED])->save();
        $this->audit->log('rejected', $user, "Rejected teacher registration for {$user->email}");

        return back()->with('success', "{$user->name}'s registration was rejected.");
    }

    /**
     * A school's admin may only act on applicants to their own school, so one
     * school cannot approve (and thereby grant record access to) another's.
     */
    private function authorizeSameSchool(User $applicant): void
    {
        $admin = auth()->user();

        abort_if(
            $admin->school_id !== null && $admin->school_id !== $applicant->school_id,
            403,
            'This applicant belongs to another school.'
        );
    }

    /** Split a display name into [first names, last name]. */
    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [$name];

        if (count($parts) === 1) {
            return [$parts[0], Str::of($parts[0])->title()];
        }

        $last = array_pop($parts);

        return [implode(' ', $parts), $last];
    }
}
