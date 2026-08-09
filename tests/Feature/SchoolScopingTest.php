<?php

namespace Tests\Feature;

use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SchoolScopingTest extends TestCase
{
    use RefreshDatabase;

    /** An approved, on-trial teacher belonging to a school. */
    private function teacherFor(School $school): User
    {
        return User::factory()->create([
            'role' => User::ROLE_TEACHER,
            'status' => User::STATUS_APPROVED,
            'school_id' => $school->id,
            'trial_ends_at' => Carbon::now()->addDays(10),
        ]);
    }

    public function test_teacher_can_add_a_student_scoped_to_their_school(): void
    {
        $school = School::factory()->create();
        $teacher = $this->teacherFor($school);

        $this->actingAs($teacher)->post(route('teacher.students.store'), [
            'lrn' => '123456789012',
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'gender' => 'Female',
            'status' => 'active',
        ])->assertRedirect(route('teacher.students.index'));

        $this->assertDatabaseHas('students', [
            'lrn' => '123456789012',
            'school_id' => $school->id,
        ]);
    }

    public function test_a_teacher_only_sees_their_own_schools_students(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $teacherA = $this->teacherFor($schoolA);
        $teacherB = $this->teacherFor($schoolB);

        // Create one student in each school (auto-stamped by the acting user).
        $this->actingAs($teacherA)->post(route('teacher.students.store'), [
            'lrn' => '111111111111', 'first_name' => 'Aaa', 'last_name' => 'Alpha', 'gender' => 'Male', 'status' => 'active',
        ]);
        $this->actingAs($teacherB)->post(route('teacher.students.store'), [
            'lrn' => '222222222222', 'first_name' => 'Bbb', 'last_name' => 'Beta', 'gender' => 'Male', 'status' => 'active',
        ]);

        $this->actingAs($teacherA)->get(route('teacher.students.index'))
            ->assertOk()
            ->assertSee('Alpha')
            ->assertDontSee('Beta');
    }

    public function test_a_teacher_cannot_edit_another_schools_student(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $teacherA = $this->teacherFor($schoolA);
        $teacherB = $this->teacherFor($schoolB);

        // Student owned by school B (created while acting as teacher B).
        $this->actingAs($teacherB)->post(route('teacher.students.store'), [
            'lrn' => '333333333333', 'first_name' => 'Ccc', 'last_name' => 'Gamma', 'gender' => 'Male', 'status' => 'active',
        ]);
        $studentB = Student::withoutGlobalScopes()->where('lrn', '333333333333')->firstOrFail();

        // Teacher A's scoped route-model binding should not resolve it.
        $this->actingAs($teacherA)->get(route('teacher.students.edit', $studentB))->assertNotFound();
    }

    public function test_teacher_can_create_an_advisory_section_from_students_page(): void
    {
        $school = School::factory()->create();
        $teacherUser = $this->teacherFor($school);
        $teacher = Teacher::factory()->create(['school_id' => $school->id, 'user_id' => $teacherUser->id]);
        $year = SchoolYear::factory()->active()->create();
        SchoolYear::forgetCurrent();
        $grade = GradeLevel::factory()->create(['name' => 'Grade 8']);

        $this->actingAs($teacherUser)
            ->get(route('teacher.students.index'))
            ->assertOk()
            ->assertSee('Add Section')
            ->assertSee('Advisory Sections');

        $this->actingAs($teacherUser)
            ->post(route('teacher.sections.store'), [
                'return_to' => 'students',
                'grade_level_id' => $grade->id,
                'name' => 'Jadeite',
                'room' => '101',
                'capacity' => 45,
            ])
            ->assertRedirect(route('teacher.students.index'));

        $this->assertDatabaseHas('sections', [
            'school_id' => $school->id,
            'school_year_id' => $year->id,
            'grade_level_id' => $grade->id,
            'adviser_id' => $teacher->id,
            'name' => 'Jadeite',
        ]);
    }

    public function test_section_create_still_returns_to_dashboard_by_default(): void
    {
        $school = School::factory()->create();
        $teacherUser = $this->teacherFor($school);
        Teacher::factory()->create(['school_id' => $school->id, 'user_id' => $teacherUser->id]);
        SchoolYear::factory()->active()->create();
        SchoolYear::forgetCurrent();
        $grade = GradeLevel::factory()->create(['name' => 'Grade 9']);

        $this->actingAs($teacherUser)
            ->post(route('teacher.sections.store'), [
                'grade_level_id' => $grade->id,
                'name' => 'Emerald',
            ])
            ->assertRedirect(route('teacher.dashboard'));
    }

    public function test_teacher_can_filter_students_by_advisory_section_and_see_counts(): void
    {
        $school = School::factory()->create();
        $teacherUser = $this->teacherFor($school);
        $teacher = Teacher::factory()->create(['school_id' => $school->id, 'user_id' => $teacherUser->id]);
        $year = SchoolYear::factory()->active()->create();
        SchoolYear::forgetCurrent();
        $grade = GradeLevel::factory()->create(['name' => 'Grade 8']);
        $jadeite = Section::factory()->create([
            'school_id' => $school->id,
            'school_year_id' => $year->id,
            'grade_level_id' => $grade->id,
            'adviser_id' => $teacher->id,
            'name' => 'Jadeite',
        ]);
        $ruby = Section::factory()->create([
            'school_id' => $school->id,
            'school_year_id' => $year->id,
            'grade_level_id' => $grade->id,
            'adviser_id' => $teacher->id,
            'name' => 'Ruby',
        ]);

        $alpha = Student::factory()->create(['school_id' => $school->id, 'last_name' => 'Alpha', 'gender' => 'Female', 'lrn' => null]);
        $beta = Student::factory()->create(['school_id' => $school->id, 'last_name' => 'Beta', 'lrn' => '123456789001', 'birthdate' => '2013-01-01', 'guardian_contact' => '09170000000', 'address' => 'Complete Address']);
        $gamma = Student::factory()->create(['school_id' => $school->id, 'last_name' => 'Gamma', 'lrn' => '123456789002', 'birthdate' => '2013-01-01', 'guardian_contact' => '09171111111', 'address' => 'Complete Address']);

        foreach ([[$alpha, $jadeite], [$beta, $jadeite], [$gamma, $ruby]] as [$student, $section]) {
            StudentEnrollment::create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'school_year_id' => $year->id,
                'grade_level_id' => $grade->id,
                'section_id' => $section->id,
                'status' => 'enrolled',
                'promotion_status' => 'pending',
                'enrollment_date' => now(),
            ]);
        }

        $this->actingAs($teacherUser)
            ->get(route('teacher.students.index'))
            ->assertOk()
            ->assertSee('2 learners')
            ->assertSee('1 learner')
            ->assertSee('Missing LRN');

        $this->actingAs($teacherUser)
            ->get(route('teacher.students.index', ['section_id' => $jadeite->id]))
            ->assertOk()
            ->assertSee('Alpha')
            ->assertSee('Beta')
            ->assertDontSee('Gamma');

        $this->actingAs($teacherUser)
            ->get(route('teacher.students.index', ['section_id' => $jadeite->id, 'needs_info' => 1]))
            ->assertOk()
            ->assertSee('Alpha')
            ->assertDontSee('Beta')
            ->assertDontSee('Gamma');
    }

    public function test_teacher_add_student_uses_selected_section(): void
    {
        $school = School::factory()->create();
        $teacherUser = $this->teacherFor($school);
        $teacher = Teacher::factory()->create(['school_id' => $school->id, 'user_id' => $teacherUser->id]);
        $year = SchoolYear::factory()->active()->create();
        SchoolYear::forgetCurrent();
        $grade = GradeLevel::factory()->create(['name' => 'Grade 8']);
        $first = Section::factory()->create([
            'school_id' => $school->id,
            'school_year_id' => $year->id,
            'grade_level_id' => $grade->id,
            'adviser_id' => $teacher->id,
            'name' => 'First',
        ]);
        $selected = Section::factory()->create([
            'school_id' => $school->id,
            'school_year_id' => $year->id,
            'grade_level_id' => $grade->id,
            'adviser_id' => $teacher->id,
            'name' => 'Selected',
        ]);

        $this->actingAs($teacherUser)
            ->post(route('teacher.students.store'), [
                'section_id' => $selected->id,
                'lrn' => '123456789099',
                'first_name' => 'Mia',
                'last_name' => 'Selected',
                'gender' => 'Female',
                'status' => 'active',
            ])
            ->assertRedirect(route('teacher.students.index', ['section_id' => $selected->id]));

        $student = Student::withoutGlobalScopes()->where('lrn', '123456789099')->firstOrFail();
        $this->assertDatabaseHas('student_enrollments', [
            'student_id' => $student->id,
            'section_id' => $selected->id,
        ]);
        $this->assertDatabaseMissing('student_enrollments', [
            'student_id' => $student->id,
            'section_id' => $first->id,
        ]);
    }

    public function test_teacher_can_import_students_into_selected_advisory_section(): void
    {
        $school = School::factory()->create();
        $teacherUser = $this->teacherFor($school);
        $teacher = Teacher::factory()->create(['school_id' => $school->id, 'user_id' => $teacherUser->id]);
        $year = SchoolYear::factory()->active()->create();
        SchoolYear::forgetCurrent();
        $grade = GradeLevel::factory()->create(['level_order' => 8, 'code' => 'G8', 'name' => 'Grade 8']);
        $section = Section::factory()->create([
            'school_id' => $school->id,
            'school_year_id' => $year->id,
            'grade_level_id' => $grade->id,
            'adviser_id' => $teacher->id,
            'name' => 'Jadeite',
        ]);

        $csv = "lrn,first_name,middle_name,last_name,gender,birthdate,address\n"
            .",Ana,,Reyes,F,,\n"
            ."123456789012,Ben,,Santos,M,2012-01-15,\n";
        $file = UploadedFile::fake()->createWithContent('students.csv', $csv);

        $this->actingAs($teacherUser)
            ->post(route('teacher.students.import'), ['section_id' => $section->id, 'file' => $file])
            ->assertRedirect();

        $this->assertDatabaseHas('students', [
            'school_id' => $school->id,
            'lrn' => null,
            'last_name' => 'Reyes',
            'gender' => 'Female',
        ]);
        $this->assertDatabaseHas('students', [
            'school_id' => $school->id,
            'lrn' => '123456789012',
            'last_name' => 'Santos',
            'gender' => 'Male',
        ]);
        $this->assertSame(2, StudentEnrollment::withoutGlobalScopes()->where('section_id', $section->id)->count());
    }

    public function test_teacher_cannot_import_students_into_non_advisory_section(): void
    {
        $school = School::factory()->create();
        $teacherUser = $this->teacherFor($school);
        Teacher::factory()->create(['school_id' => $school->id, 'user_id' => $teacherUser->id]);
        $year = SchoolYear::factory()->active()->create();
        SchoolYear::forgetCurrent();
        $grade = GradeLevel::factory()->create();
        $section = Section::factory()->create([
            'school_id' => $school->id,
            'school_year_id' => $year->id,
            'grade_level_id' => $grade->id,
            'adviser_id' => null,
        ]);
        $file = UploadedFile::fake()->createWithContent('students.csv', "first_name,last_name,gender\nAna,Reyes,F\n");

        $this->actingAs($teacherUser)
            ->from(route('teacher.students.index'))
            ->post(route('teacher.students.import'), ['section_id' => $section->id, 'file' => $file])
            ->assertRedirect(route('teacher.students.index'))
            ->assertSessionHasErrors('section_id');

        $this->assertDatabaseMissing('students', ['last_name' => 'Reyes']);
    }

    public function test_two_schools_can_reuse_the_same_subject_code(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();

        $this->actingAs($this->teacherFor($schoolA))->post(route('teacher.subjects.store'), [
            'name' => 'Mathematics', 'code' => 'MATH7', 'is_active' => 1,
        ])->assertRedirect(route('teacher.subjects.index'));

        // Same code in a different school must be allowed.
        $this->actingAs($this->teacherFor($schoolB))->post(route('teacher.subjects.store'), [
            'name' => 'Mathematics', 'code' => 'MATH7', 'is_active' => 1,
        ])->assertRedirect(route('teacher.subjects.index'));

        $this->assertSame(2, Subject::withoutGlobalScopes()->where('code', 'MATH7')->count());
    }

    public function test_duplicate_subject_code_within_a_school_is_rejected(): void
    {
        $school = School::factory()->create();
        $teacher = $this->teacherFor($school);

        $this->actingAs($teacher)->post(route('teacher.subjects.store'), [
            'name' => 'Science', 'code' => 'SCI7', 'is_active' => 1,
        ]);

        $this->actingAs($teacher)->post(route('teacher.subjects.store'), [
            'name' => 'Science II', 'code' => 'SCI7', 'is_active' => 1,
        ])->assertSessionHasErrors('code');
    }
}
