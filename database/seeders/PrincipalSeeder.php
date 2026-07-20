<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * A ready-to-use school head (principal) for school_id = 5. A supervisor is a
 * read-only oversight account: it can view and print every teacher's records in
 * its own school but never edit them, and it is confined to that school by the
 * tenant scope. No trial, no billing, no Teacher record — approval alone grants
 * access. Idempotent: keyed on the email, so re-seeding never duplicates it.
 *
 * Target another school with PRINCIPAL_SCHOOL_ID; override the login with
 * PRINCIPAL_EMAIL / PRINCIPAL_PASSWORD.
 */
class PrincipalSeeder extends Seeder
{
    public function run(): void
    {
        $schoolId = (int) env('PRINCIPAL_SCHOOL_ID', 5);

        $school = School::find($schoolId);

        if (! $school) {
            throw new \RuntimeException(
                "No school with id={$schoolId}. Run SchoolSeeder first, or set PRINCIPAL_SCHOOL_ID to an existing school."
            );
        }

        $email = env('PRINCIPAL_EMAIL', 'principal5@gmail.com');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Principal · '.$school->name,
                'password' => Hash::make(env('PRINCIPAL_PASSWORD', 'password')),
                'role' => User::ROLE_SUPERVISOR,
                'is_active' => true,
                'status' => User::STATUS_APPROVED,
                'email_verified_at' => now(),

                // Oversight account: never enters the billing funnel, so it reads
                // as "managed" and is exempt from the paywall.
                'trial_ends_at' => null,
                'free_access' => false,
                'subscribed_until' => null,
                'subscription_plan' => null,

                'school_id' => $school->id,
                // Seeded accounts are pre-verified — no ID document to review.
                'approved_at' => Carbon::now(),
                'school_id_verified_at' => Carbon::now(),
            ]
        );
    }
}
