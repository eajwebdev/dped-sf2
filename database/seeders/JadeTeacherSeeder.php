<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Default teacher: Jade D. Samillano, adviser of Grade 8 — JADEITE in the
 * active school year, with her real class roster enrolled. Idempotent:
 * learners are keyed on deterministic placeholder LRNs (8260…), so
 * re-seeding never duplicates anyone. Replace LRNs with real ones by
 * editing the learner — the seeder only fills blanks, it never overwrites.
 */
class JadeTeacherSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * Jade teaches in 2026-2027. Resolved by name rather than by id — ids
         * are not stable across a fresh install — falling back to the active
         * year, then the newest. Set JADE_SCHOOL_YEAR to target another.
         */
        $wantYear = env('JADE_SCHOOL_YEAR', '2026-2027');

        $sy = SchoolYear::where('name', $wantYear)->first()
            ?? SchoolYear::active()->first()
            ?? SchoolYear::orderByDesc('start_date')->firstOrFail();

        $g8 = GradeLevel::where('code', 'G8')->firstOrFail();

        /*
         * Every teacher belongs to a school. The tenant scope fails closed, so
         * an account without one sees nothing at all — this seeder used to
         * create exactly that, leaving Jade unable to open her own class.
         * Defaults to the first school; set JADE_SCHOOL to pick another, by
         * DepEd School ID ("303244") or by name, exact or partial.
         */
        $want = env('JADE_SCHOOL');

        $school = School::query()
            ->when($want, fn ($q) => $q->where(fn ($w) => $w
                ->where('school_id', $want)
                ->orWhere('name', $want)
                ->orWhere('name', 'like', '%'.$want.'%')))
            ->orderBy('id')
            ->first();

        if (! $school) {
            // Without a school nothing this seeder writes is readable, so stop
            // with an instruction rather than an opaque ModelNotFoundException.
            throw new \RuntimeException($want
                ? "No school matches JADE_SCHOOL=\"{$want}\"."
                : 'No schools exist — run SchoolSeeder first (php artisan db:seed --class=SchoolSeeder).');
        }

        /*
         * Point the school at the year we just seeded into. Without this the
         * app resolves Jade's year through SchoolYear::activeFor() — her
         * school's override, else the globally active year — which is still
         * 2025-2026, so her advisory would exist but render nowhere. Only this
         * one school is pinned; the global active year is left alone so the
         * other 13 tenants are unaffected.
         */
        $school->forceFill(['active_school_year_id' => 2])->save();

        $user = User::updateOrCreate(
            ['email' => 'jade@gmail.com'],
            [
                'name' => 'Jade D. Samillano',
                'password' => Hash::make('password'),
                'role' => User::ROLE_TEACHER,
                'is_active' => true,
                'status' => User::STATUS_APPROVED,
                'email_verified_at' => now(),

                // Seeded mid-trial so the countdown banner and the full module
                // set are both exercised out of the box. Without a
                // trial_ends_at she reads as "managed" — unlimited access, no
                // countdown — which is not what a real signup looks like.
                // free_access / subscribed_until are pinned so a re-seed of an
                // account that was comped or paid lands back on 'trial'.
                'trial_ends_at' => now()->addDays(User::TRIAL_DAYS),
                'free_access' => false,
                'subscribed_until' => null,
                'subscription_plan' => null,
                'school_id' => 5,
            ]
        );

        $teacher = Teacher::updateOrCreate(
            ['user_id' => $user->id],
            [
                'employee_no' => 'T-00002',
                'first_name' => 'Jade',
                'middle_name' => 'D.',
                'last_name' => 'Samillano',
                'gender' => 'Female',
                'email' => $user->email,
                'is_active' => true,
                'school_id' => 5,
            ]
        );

        /*
         * Her advisory class. Keyed on the adviser, not the school. Matching name alone would find
         * another school's "JADEITE" and hand it to Jade; adding school_id to
         * the key instead would fork a second section whenever JADE_SCHOOL
         * changes, leaving the roster enrolled in the abandoned one. Keying on
         * her teacher id moves the one section she owns.
         */
        $section = Section::withoutGlobalScopes()->updateOrCreate(
            ['adviser_id' => $teacher->id, 'school_year_id' => 2, 'grade_level_id' => $g8->id, 'name' => 'JADEITE'],
            ['school_id' => 5]
        );

        // Roster: [last, first, middle initial, suffix] — SF2 order, males then females.
        $males = [
            ['Babor', 'Justine', 'N.', null],
            ['Babor', 'Rayand', 'M.', null],
            ['Bantayao', 'Jebby', null, null],
            ['Bartolome', 'John Mike', 'C.', null],
            ['Bona', 'Prince Rhiu Jiie', 'B.', null],
            ['Buctolan', 'Vince Lester', 'R.', null],
            ['Butalid', 'Jeanzen', 'E.', null],
            ['Claro', 'Federico', 'M.', 'Jr.'],
            ['De Jesus', 'Melvin', 'B.', null],
            ['Deguit', 'Ryniel Dave', 'N.', null],
            ['Deguit', 'Jongie', 'B.', null],
            ['Deimos', 'Loriege', 'B.', null],
            ['Guarin', 'Jimrex', 'Z.', null],
            ['Guzman', 'Renan', 'D.', null],
            ['Libaton', 'Francis', 'D.', null],
            ['Lubao', 'Jemier', 'M.', null],
            ['Macadildig', 'Ralph', 'E.', null],
            ['Mahinay', 'Rhein Jhones', null, null],
            ['Miran', 'Crisol', 'J.', 'Jr.'],
            ['Nasarino', 'John Louise', 'T.', null],
            ['Obido', 'Jake Roem', 'O.', null],
            ['Providencia', 'Jhonrex', null, null],
            ['Reposo', 'Jordan', 'E.', null],
            ['Romano', 'Sammy Dale', null, null],
            ['Timone', 'Janiel Jay', 'M.', null],
            ['Vidad', 'Menard', 'T.', null],
        ];
        $females = [
            ['Babor', 'Creshel Mae', 'P.', null],
            ['Baldado', 'Reajcy', 'T.', null],
            ['Dela Cruz', 'Jas', null, null],
            ['Dela Cruz', 'Ma. Liah', null, null],
            ['Devero', 'Jasmen', 'P.', null],
            ['Duremdez', 'Jelaica', null, null],
            ['Geverola', 'Jewel', 'C.', null],
            ['Gregorio', 'Ginibeb', 'G.', null],
            ['Mamac', 'Vanjelyn', 'P.', null],
            ['Maraño', 'Marshelyn', 'A.', null],
            ['Marino', 'Relaica Rose', 'D.', null],
            ['Minguito', 'Jesa Mae', 'B.', null],
            ['Mission', 'Joanna', 'A.', null],
            ['Oracion', 'Mayvic', 'B.', null],
            ['Oray', 'Renie Mae', 'A.', null],
            ['Reposo', 'Jhyzer-Belle', 'D.', null],
            ['Solatorio', 'Laiza', 'G.', null],
            ['Suan', 'Danica', 'C.', null],
            ['Tahum', 'Marian Grace', 'Y.', null],
            ['Vicente', 'Mialyn', 'L.', null],
            ['Yabog', 'Chance', 'J.', null],
            ['Ybañez', 'Cheska Denise', 'R.', null],
        ];

        $i = 0;
        foreach ([['Male', $males], ['Female', $females]] as [$gender, $rows]) {
            foreach ($rows as [$last, $first, $middle, $suffix]) {
                $i++;
                // Deterministic placeholder LRN (12 digits) = stable idempotency key.
                $student = Student::firstOrCreate(
                    ['lrn' => sprintf('8260%08d', $i)],
                    [
                        'first_name' => $first,
                        'middle_name' => $middle,
                        'last_name' => $last,
                        'suffix' => $suffix,
                        'gender' => $gender,
                        'school_id' => 5,
                    ]
                );

                StudentEnrollment::firstOrCreate(
                    ['student_id' => $student->id, 'school_year_id' => 2],
                    [
                        'grade_level_id' => $g8->id,
                        'section_id' => $section->id,
                        'status' => StudentEnrollment::STATUS_ENROLLED,
                        'promotion_status' => 'pending',
                        'enrollment_date' => $sy->start_date,
                        'school_id' => 5,
                    ]
                );
            }
        }

        /*
         * Repair rows seeded before every record needed a school. firstOrCreate
         * leaves an existing row untouched, so learners created by an earlier
         * run would keep a null school_id — and the scope now hides those from
         * Jade herself. Rows pointing at a *different* school are realigned
         * too, so re-running with a new JADE_SCHOOL moves the whole class
         * rather than stranding the roster behind Jade's new tenant scope.
         * Only rows this seeder owns are touched.
         */
        Student::withoutGlobalScopes()
            ->where('lrn', 'like', '8260%')
            ->where(fn ($q) => $q->whereNull('school_id')->orWhere('school_id', '!=', 5))
            ->update(['school_id' => 5]);

        foreach ([StudentEnrollment::class, Attendance::class, ClassSession::class] as $model) {
            $model::withoutGlobalScopes()
                ->where('section_id', $section->id)
                ->where(fn ($q) => $q->whereNull('school_id')->orWhere('school_id', '!=', 5))
                ->update(['school_id' => 5]);
        }
    }
}
