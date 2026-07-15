<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use App\Models\User;
use App\Services\SchoolCalendarService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $sy = SchoolYear::where('name', '2025-2026')->firstOrFail();
        $g7 = GradeLevel::where('code', 'G7')->firstOrFail();

        // Teacher with a login account.
        $teacherUser = User::updateOrCreate(
            ['email' => 'teacher@dpch.edu.ph'],
            [
                'name' => 'Maria Santos',
                'password' => Hash::make('password'),
                'role' => User::ROLE_TEACHER,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $teacher = Teacher::updateOrCreate(
            ['user_id' => $teacherUser->id],
            [
                'employee_no' => 'T-00001',
                'first_name' => 'Maria',
                'middle_name' => 'Reyes',
                'last_name' => 'Santos',
                'gender' => 'Female',
                'email' => $teacherUser->email,
                'contact' => '09171234567',
                'is_active' => true,
            ]
        );

        // Section with the teacher as adviser.
        $section = Section::updateOrCreate(
            ['school_year_id' => $sy->id, 'grade_level_id' => $g7->id, 'name' => 'Rizal'],
            ['adviser_id' => $teacher->id, 'room' => 'Room 101', 'capacity' => 45]
        );

        // Subject offerings for the section + assign the teacher to each.
        $subjects = Subject::where('grade_level_id', $g7->id)->get();
        foreach ($subjects as $subject) {
            $sa = SubjectAssignment::updateOrCreate([
                'section_id' => $section->id,
                'subject_id' => $subject->id,
            ], [
                'school_year_id' => $sy->id,
                'grade_level_id' => $g7->id,
            ]);

            TeacherSubjectAssignment::updateOrCreate([
                'subject_assignment_id' => $sa->id,
                'teacher_id' => $teacher->id,
            ], ['is_primary' => true]);
        }

        // Learners + enrollments (idempotent-ish: only seed if section is empty).
        if ($section->enrollments()->count() === 0) {
            $students = Student::factory()->count(30)->create();
            foreach ($students as $student) {
                StudentEnrollment::create([
                    'student_id' => $student->id,
                    'school_year_id' => $sy->id,
                    'grade_level_id' => $g7->id,
                    'section_id' => $section->id,
                    'status' => StudentEnrollment::STATUS_ENROLLED,
                    'promotion_status' => 'pending',
                    'enrollment_date' => $sy->start_date,
                ]);
            }
        }

        // A sample day of attendance on the first class day of the year.
        $firstClassDay = app(SchoolCalendarService::class)
            ->classDays($sy, $sy->start_date, $sy->end_date)
            ->first();

        if ($firstClassDay) {
            foreach ($section->enrollments()->with('student')->get() as $i => $enrollment) {
                $status = match (true) {
                    $i % 13 === 0 => Attendance::STATUS_ABSENT,
                    $i % 7 === 0 => Attendance::STATUS_LATE,
                    default => Attendance::STATUS_PRESENT,
                };

                Attendance::updateOrCreate(
                    ['student_enrollment_id' => $enrollment->id, 'attendance_date' => $firstClassDay],
                    [
                        'student_id' => $enrollment->student_id,
                        'section_id' => $section->id,
                        'school_year_id' => $sy->id,
                        'status' => $status,
                        'marked_by' => $teacherUser->id,
                    ]
                );
            }
        }
    }
}
