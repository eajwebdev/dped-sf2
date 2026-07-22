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
                // values[enrollment][core_value][behavior][period] = mark
                'values' => [$enrollment->id => ['maka_diyos' => [1 => [1 => 'AO', 2 => 'SO'], 2 => [1 => 'RO']]]],
            ])->assertRedirect();

        $this->assertDatabaseHas('learner_grades', [
            'student_enrollment_id' => $enrollment->id, 'subject_id' => $subject->id, 'period' => 1, 'grade' => 90,
        ]);
        $this->assertDatabaseHas('learner_values', [
            'student_enrollment_id' => $enrollment->id, 'core_value' => 'maka_diyos', 'behavior' => 1, 'period' => 1, 'mark' => 'AO',
        ]);
        $this->assertDatabaseHas('learner_values', [
            'student_enrollment_id' => $enrollment->id, 'core_value' => 'maka_diyos', 'behavior' => 2, 'period' => 1, 'mark' => 'RO',
        ]);
    }

    public function test_save_ignores_a_behavior_index_the_core_value_does_not_have(): void
    {
        $enrollment = $this->enroll();

        // Maka-kalikasan has only one behaviour statement; behaviour 2 is invalid.
        $this->actingAs($this->adviserUser)
            ->post(route('teacher.sf9.grades.save', $this->section), [
                'values' => [$enrollment->id => ['maka_kalikasan' => [2 => [1 => 'AO']]]],
            ])->assertRedirect();

        $this->assertDatabaseMissing('learner_values', [
            'student_enrollment_id' => $enrollment->id, 'core_value' => 'maka_kalikasan', 'behavior' => 2,
        ]);
    }

    public function test_report_service_lists_official_behavior_statements(): void
    {
        $enrollment = $this->enroll();
        LearnerValue::create([
            'school_id' => $this->section->school_id,
            'student_enrollment_id' => $enrollment->id,
            'core_value' => 'maka_diyos', 'behavior' => 2, 'period' => 3, 'mark' => 'SO',
        ]);

        $data = app(Sf9ReportService::class)->build($this->section);
        $values = $data['learners'][0]['values'];

        // Maka-Diyos carries its two official behaviour statements as sub-rows.
        $this->assertCount(2, $values['maka_diyos']['statements']);
        $this->assertSame(1, $values['maka_kalikasan']['statements'][0]['behavior']);
        $this->assertSame('SO', $values['maka_diyos']['statements'][1]['marks'][3]);
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

    public function test_final_rating_is_blank_until_all_four_quarters_are_entered(): void
    {
        $enrollment = $this->enroll();
        $subject = $this->assignSubject();

        // Only three of the four quarters are in.
        foreach ([1 => 90, 2 => 85, 3 => 88] as $period => $grade) {
            LearnerGrade::create([
                'school_id' => $this->section->school_id,
                'student_enrollment_id' => $enrollment->id,
                'subject_id' => $subject->id, 'period' => $period, 'grade' => $grade,
            ]);
        }

        $learner = app(Sf9ReportService::class)->build($this->section)['learners'][0];

        $this->assertNull($learner['subjects'][0]['final'], 'Final must stay blank until all four quarters are entered.');
        $this->assertSame('', $learner['subjects'][0]['remark']);
        $this->assertNull($learner['generalAverage']['final']);

        // Adding the fourth quarter completes it.
        LearnerGrade::create([
            'school_id' => $this->section->school_id,
            'student_enrollment_id' => $enrollment->id,
            'subject_id' => $subject->id, 'period' => 4, 'grade' => 81,
        ]);

        $learner = app(Sf9ReportService::class)->build($this->section)['learners'][0];
        $this->assertSame(86, $learner['subjects'][0]['final']); // (90+85+88+81)/4 = 86
    }

    public function test_level_tag_reflects_the_grade_level(): void
    {
        $service = app(Sf9ReportService::class);

        // The setUp section is Grade 8 → JHS.
        $this->assertSame('JHS', $service->levelTag($this->section));

        $es = Section::factory()->create([
            'school_id' => $this->section->school_id, 'school_year_id' => $this->year->id,
            'grade_level_id' => GradeLevel::factory()->create(['level_order' => 5, 'code' => 'G5'])->id,
            'adviser_id' => $this->adviser->id,
        ]);
        $this->assertSame('ES', $service->levelTag($es));

        $shs = Section::factory()->create([
            'school_id' => $this->section->school_id, 'school_year_id' => $this->year->id,
            'grade_level_id' => GradeLevel::factory()->create(['level_order' => 11, 'code' => 'G11X'])->id,
            'adviser_id' => $this->adviser->id,
        ]);
        $this->assertSame('SHS', $service->levelTag($shs));
        $this->assertTrue($service->build($shs)['isShs']);
        $this->assertSame('SHS', $service->build($shs)['levelTag']);
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

    public function test_adviser_can_open_sf9_for_a_single_learner(): void
    {
        $one = $this->enroll();
        $this->enroll();
        $this->assignSubject();

        $response = $this->actingAs($this->adviserUser)
            ->get(route('reports.sf9.show', ['section' => $this->section, 'student' => $one->id]));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_sf9_single_learner_404s_when_not_in_the_section(): void
    {
        $this->enroll();

        $this->actingAs($this->adviserUser)
            ->get(route('reports.sf9.show', ['section' => $this->section, 'student' => 999999]))
            ->assertNotFound();
    }

    public function test_non_adviser_cannot_open_or_edit(): void
    {
        $stranger = User::factory()->create(['role' => User::ROLE_TEACHER, 'is_active' => true]);
        Teacher::factory()->create(['user_id' => $stranger->id, 'school_id' => $stranger->school_id]);

        $this->actingAs($stranger)->get(route('reports.sf9.show', $this->section))->assertForbidden();
        $this->actingAs($stranger)->get(route('teacher.sf9.grades', $this->section))->assertForbidden();
    }
}
