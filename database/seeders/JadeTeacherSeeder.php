<?php

namespace Database\Seeders;

use App\Models\GradeLevel;
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
        $sy = SchoolYear::active()->first() ?? SchoolYear::orderByDesc('start_date')->firstOrFail();
        $g8 = GradeLevel::where('code', 'G8')->firstOrFail();

        $user = User::updateOrCreate(
            ['email' => 'jade@dpch.edu.ph'],
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
            ]
        );

        // Her advisory class.
        $section = Section::updateOrCreate(
            ['school_year_id' => $sy->id, 'grade_level_id' => $g8->id, 'name' => 'JADEITE'],
            ['adviser_id' => $teacher->id]
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
                        'status' => 'active',
                    ]
                );

                StudentEnrollment::firstOrCreate(
                    ['student_id' => $student->id, 'school_year_id' => $sy->id],
                    [
                        'grade_level_id' => $g8->id,
                        'section_id' => $section->id,
                        'status' => StudentEnrollment::STATUS_ENROLLED,
                        'promotion_status' => 'pending',
                        'enrollment_date' => $sy->start_date,
                    ]
                );
            }
        }
    }
}
