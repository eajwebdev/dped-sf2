<?php

namespace Tests\Feature;

use App\Models\GradeLevel;
use App\Models\LearnerGrade;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Sf10ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sf10ReportTest extends TestCase
{
    use RefreshDatabase;

    private SchoolYear $year;

    private User $adviserUser;

    private Teacher $adviser;

    private Section $section;

    protected function setUp(): void
    {
        parent::setUp();

        $this->year = SchoolYear::factory()->active()->create([
            'start_date' => '2025-06-01', 'end_date' => '2026-03-31',
        ]);

        $this->adviserUser = User::factory()->create(['role' => User::ROLE_TEACHER, 'is_active' => true]);
        $this->adviser = Teacher::factory()->create(['user_id' => $this->adviserUser->id, 'school_id' => $this->adviserUser->school_id]);

        $grade = GradeLevel::factory()->create(['level_order' => 5, 'code' => 'G5']);
        $this->section = Section::factory()->create([
            'school_id' => $this->adviserUser->school_id,
            'school_year_id' => $this->year->id,
            'grade_level_id' => $grade->id,
            'adviser_id' => $this->adviser->id,
        ]);
    }

    private function enroll(string $gender = 'Male'): StudentEnrollment
    {
        $student = Student::factory()->create(['gender' => $gender, 'school_id' => $this->section->school_id]);

        return StudentEnrollment::create([
            'student_id' => $student->id, 'school_year_id' => $this->year->id,
            'grade_level_id' => $this->section->grade_level_id, 'section_id' => $this->section->id,
            'status' => 'enrolled', 'enrollment_date' => '2025-06-01',
        ]);
    }

    private function assignSubject(string $name = 'Mathematics'): Subject
    {
        $subject = Subject::factory()->create(['name' => $name, 'school_id' => $this->section->school_id]);
        SubjectAssignment::create([
            'school_id' => $this->section->school_id,
            'school_year_id' => $this->section->school_year_id,
            'grade_level_id' => $this->section->grade_level_id,
            'section_id' => $this->section->id,
            'subject_id' => $subject->id,
        ]);

        return $subject;
    }

    private function grade(StudentEnrollment $enrollment, Subject $subject, array $byPeriod): void
    {
        foreach ($byPeriod as $period => $grade) {
            LearnerGrade::create([
                'school_id' => $this->section->school_id,
                'student_enrollment_id' => $enrollment->id,
                'subject_id' => $subject->id, 'period' => $period, 'grade' => $grade,
            ]);
        }
    }

    public function test_service_carries_personal_information(): void
    {
        $enrollment = $this->enroll('Female');
        $enrollment->student->update(['last_name' => 'Dela Cruz', 'first_name' => 'Maria', 'lrn' => '123456789012']);

        $learner = app(Sf10ReportService::class)->build($this->section)['learners'][0];

        $this->assertSame('Dela Cruz', $learner['lastName']);
        $this->assertSame('Maria', $learner['firstName']);
        $this->assertSame('123456789012', $learner['lrn']);
        $this->assertSame('Female', $learner['sex']);
    }

    /** The learning-area row on the SF10-ES scholastic block for a given label. */
    private function area(array $learner, string $label): array
    {
        foreach ($learner['areas'] as $row) {
            if ($row['label'] === $label) {
                return $row;
            }
        }

        $this->fail("No learning-area row labelled {$label}.");
    }

    public function test_grades_map_onto_the_standard_learning_area_row(): void
    {
        $enrollment = $this->enroll();
        $subject = $this->assignSubject('Mathematics');
        $this->grade($enrollment, $subject, [1 => 90, 2 => 80, 3 => 90, 4 => 80]);

        $learner = app(Sf10ReportService::class)->build($this->section)['learners'][0];
        $math = $this->area($learner, 'Mathematics');

        $this->assertSame(85, $math['final']); // (90+80+90+80)/4
        $this->assertSame('PASSED', $math['remark']);
        $this->assertSame(85, $learner['generalAverage']);
        $this->assertSame('PASSED', $learner['generalRemark']);
    }

    public function test_final_rating_is_blank_until_all_four_quarters_are_entered(): void
    {
        $enrollment = $this->enroll();
        $subject = $this->assignSubject('Mathematics');
        $this->grade($enrollment, $subject, [1 => 90, 2 => 85, 3 => 88]); // only three

        $learner = app(Sf10ReportService::class)->build($this->section)['learners'][0];
        $math = $this->area($learner, 'Mathematics');

        $this->assertNull($math['final']);
        $this->assertSame('', $math['remark']);
        $this->assertNull($learner['generalAverage']);
    }

    public function test_an_unmatched_subject_falls_into_a_blank_row(): void
    {
        $enrollment = $this->enroll();
        $subject = $this->assignSubject('Robotics'); // not a standard learning area
        $this->grade($enrollment, $subject, [1 => 88, 2 => 88, 3 => 88, 4 => 88]);

        $learner = app(Sf10ReportService::class)->build($this->section)['learners'][0];

        // No standard row named Robotics — its rating must still print in a blank row.
        $blankWithGrade = collect($learner['areas'])
            ->first(fn ($r) => $r['label'] === '' && $r['final'] === 88);
        $this->assertNotNull($blankWithGrade, 'Unmatched subject grade should land in a blank row.');
    }

    public function test_adviser_can_open_sf10_pdf(): void
    {
        $enrollment = $this->enroll();
        $subject = $this->assignSubject();
        $this->grade($enrollment, $subject, [1 => 90, 2 => 80, 3 => 90, 4 => 80]);

        $response = $this->actingAs($this->adviserUser)
            ->get(route('reports.sf10.show', $this->section));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_adviser_can_open_sf10_for_a_single_learner(): void
    {
        $one = $this->enroll();
        $this->enroll();
        $subject = $this->assignSubject('Mathematics');
        $this->grade($one, $subject, [1 => 90, 2 => 80, 3 => 90, 4 => 80]);

        $response = $this->actingAs($this->adviserUser)
            ->get(route('reports.sf10.show', ['section' => $this->section, 'student' => $one->id]));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_sf10_single_learner_404s_when_not_in_the_section(): void
    {
        $this->enroll();

        $this->actingAs($this->adviserUser)
            ->get(route('reports.sf10.show', ['section' => $this->section, 'student' => 999999]))
            ->assertNotFound();
    }

    public function test_non_adviser_cannot_open_sf10(): void
    {
        $stranger = User::factory()->create(['role' => User::ROLE_TEACHER, 'is_active' => true]);
        Teacher::factory()->create(['user_id' => $stranger->id, 'school_id' => $stranger->school_id]);

        $this->actingAs($stranger)->get(route('reports.sf10.show', $this->section))->assertForbidden();
    }
}
