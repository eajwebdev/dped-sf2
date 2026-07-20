<?php

namespace Tests\Feature;

use App\Models\GradeLevel;
use App\Models\LearnerGrade;
use App\Models\LearnerValue;
use App\Models\SchoolCalendar;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Models\Teacher;
use App\Models\User;
use App\Services\SchoolCalendarService;
use App\Services\Sf9ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sf9ReportTest extends TestCase
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
        app(SchoolCalendarService::class)->generate($this->year);

        $this->adviserUser = User::factory()->create(['role' => User::ROLE_TEACHER, 'is_active' => true]);
        $this->adviser = Teacher::factory()->create(['user_id' => $this->adviserUser->id, 'school_id' => $this->adviserUser->school_id]);

        $grade = GradeLevel::factory()->create(['level_order' => 8, 'code' => 'G8']);
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

    public function test_adviser_can_add_a_learning_area(): void
    {
        $this->actingAs($this->adviserUser)
            ->post(route('teacher.sf9.subjects.store', $this->section), ['name' => 'Science'])
            ->assertRedirect();

        $this->assertDatabaseHas('subjects', ['name' => 'Science']);
        $this->assertDatabaseHas('subject_assignments', ['section_id' => $this->section->id]);
    }

    public function test_adviser_can_save_grades_and_values(): void
    {
        $enrollment = $this->enroll();
        $subject = $this->assignSubject();

        $this->actingAs($this->adviserUser)
            ->post(route('teacher.sf9.grades.save', $this->section), [
                'grades' => [$enrollment->id => [$subject->id => [1 => 90, 2 => 85, 3 => 88, 4 => 87]]],
                'values' => [$enrollment->id => ['maka_diyos' => [1 => 'AO', 2 => 'SO']]],
            ])->assertRedirect();

        $this->assertDatabaseHas('learner_grades', [
            'student_enrollment_id' => $enrollment->id, 'subject_id' => $subject->id, 'period' => 1, 'grade' => 90,
        ]);
        $this->assertDatabaseHas('learner_values', [
            'student_enrollment_id' => $enrollment->id, 'core_value' => 'maka_diyos', 'period' => 1, 'mark' => 'AO',
        ]);
    }

    public function test_report_service_computes_jhs_final_and_general_average(): void
    {
        $enrollment = $this->enroll();
        $subject = $this->assignSubject();

        foreach ([1 => 90, 2 => 80, 3 => 90, 4 => 80] as $period => $grade) {
            LearnerGrade::create([
                'school_id' => $this->section->school_id,
                'student_enrollment_id' => $enrollment->id,
                'subject_id' => $subject->id, 'period' => $period, 'grade' => $grade,
            ]);
        }

        $data = app(Sf9ReportService::class)->build($this->section);
        $learner = $data['learners'][0];

        $this->assertFalse($data['isShs']);
        $this->assertSame(85, $learner['subjects'][0]['final']); // (90+80+90+80)/4
        $this->assertSame('Passed', $learner['subjects'][0]['remark']);
        $this->assertSame(85, $learner['generalAverage']['final']);
    }

    public function test_senior_high_section_is_semestral(): void
    {
        $shsGrade = GradeLevel::factory()->create(['level_order' => 11, 'code' => 'G11']);
        $shs = Section::factory()->create([
            'school_id' => $this->section->school_id,
            'school_year_id' => $this->year->id,
            'grade_level_id' => $shsGrade->id,
            'adviser_id' => $this->adviser->id,
        ]);

        $data = app(Sf9ReportService::class)->build($shs);

        $this->assertTrue($data['isShs']);
        $this->assertSame('1st Sem · Q1', $data['periodLabels'][1]);
    }

    public function test_adviser_can_open_sf9_pdf(): void
    {
        $this->enroll();
        $this->assignSubject();

        $response = $this->actingAs($this->adviserUser)
            ->get(route('reports.sf9.show', $this->section));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_non_adviser_cannot_open_or_edit(): void
    {
        $stranger = User::factory()->create(['role' => User::ROLE_TEACHER, 'is_active' => true]);
        Teacher::factory()->create(['user_id' => $stranger->id, 'school_id' => $stranger->school_id]);

        $this->actingAs($stranger)->get(route('reports.sf9.show', $this->section))->assertForbidden();
        $this->actingAs($stranger)->get(route('teacher.sf9.grades', $this->section))->assertForbidden();
    }
}
