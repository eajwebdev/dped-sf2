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
        $pending = User::with('school')
            ->where('role', User::ROLE_TEACHER)
            ->where('status', User::STATUS_PENDING)
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

        DB::transaction(function () use ($user) {
            $user->forceFill([
                'status' => User::STATUS_APPROVED,
                'trial_ends_at' => Carbon::now()->addDays(User::TRIAL_DAYS),
                'approved_at' => Carbon::now(),
                'approved_by' => auth()->id(),
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

        $this->audit->log('approved', $user, "Approved teacher registration for {$user->email}");

        return back()->with('success', "{$user->name} approved — 14-day trial started.");
    }

    public function reject(User $user): RedirectResponse
    {
        abort_unless($user->isTeacher(), 404);

        $user->forceFill(['status' => User::STATUS_REJECTED])->save();
        $this->audit->log('rejected', $user, "Rejected teacher registration for {$user->email}");

        return back()->with('success', "{$user->name}'s registration was rejected.");
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
