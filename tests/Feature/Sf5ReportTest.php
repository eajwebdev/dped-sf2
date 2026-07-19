<?php

namespace Tests\Feature;

use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;
use App\Services\Sf5ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sf5ReportTest extends TestCase
{
    use RefreshDatabase;

    private SchoolYear $year;

    private Section $section;

    private User $adviser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->year = SchoolYear::factory()->active()->create([
            'start_date' => '2025-06-01', 'end_date' => '2026-03-31',
        ]);
        $grade = GradeLevel::factory()->create(['level_order' => 8, 'code' => 'G8']);

        $this->adviser = User::factory()->create(['role' => 'teacher', 'is_active' => true, 'status' => User::STATUS_APPROVED]);
        $teacher = Teacher::factory()->create(['user_id' => $this->adviser->id]);

        $this->section = Section::factory()->create([
            'school_year_id' => $this->year->id,
            'grade_level_id' => $grade->id,
            'adviser_id' => $teacher->id,
        ]);
    }

    private function enroll(string $gender, array $attributes = [], array $student = []): StudentEnrollment
    {
        $s = Student::factory()->create($student + ['gender' => $gender]);

        return StudentEnrollment::create($attributes + [
            'student_id' => $s->id,
            'school_year_id' => $this->year->id,
            'grade_level_id' => $this->section->grade_level_id,
            'section_id' => $this->section->id,
            'status' => 'enrolled',
            'promotion_status' => 'pending',
            'enrollment_date' => '2025-06-01',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Band and formatting rules
    |--------------------------------------------------------------------------
    */

    public function test_proficiency_bands_split_on_the_official_boundaries(): void
    {
        $svc = app(Sf5ReportService::class);

        $this->assertSame('B', $svc->band(70.0));
        $this->assertSame('B', $svc->band(74.9));   // "74% and below" includes fractions under 75
        $this->assertSame('D', $svc->band(75.0));
        $this->assertSame('D', $svc->band(79.9));
        $this->assertSame('AP', $svc->band(80.0));
        $this->assertSame('P', $svc->band(85.0));
        $this->assertSame('P', $svc->band(89.999));
        $this->assertSame('A', $svc->band(90.0));
        $this->assertSame('A', $svc->band(98.5));
        $this->assertSame('', $svc->band(null));
    }

    public function test_honor_learners_print_three_decimals_others_two(): void
    {
        $svc = app(Sf5ReportService::class);

        $this->assertSame('92.375 (A)', $svc->formattedAverage(92.375));
        $this->assertSame('83.50 (AP)', $svc->formattedAverage(83.5));
        $this->assertSame('74.50 (B)', $svc->formattedAverage(74.5));
        $this->assertSame('', $svc->formattedAverage(null));
    }

    public function test_action_taken_is_derived_and_irregular_overrides(): void
    {
        $svc = app(Sf5ReportService::class);

        $promoted = $this->enroll('Male', ['promotion_status' => 'promoted']);
        $retained = $this->enroll('Male', ['promotion_status' => 'retained']);
        $graduated = $this->enroll('Male', ['promotion_status' => 'graduated']);
        $irregular = $this->enroll('Male', ['promotion_status' => 'promoted', 'is_irregular' => true]);
        $pending = $this->enroll('Male');

        $this->assertSame('PROMOTED', $svc->action($promoted));
        $this->assertSame('RETAINED', $svc->action($retained));
        $this->assertSame('PROMOTED', $svc->action($graduated));
        $this->assertSame('*IRREGULAR', $svc->action($irregular));
        $this->assertSame('', $svc->action($pending));
    }

    public function test_summaries_count_actions_and_bands_by_sex(): void
    {
        $this->enroll('Male', ['promotion_status' => 'promoted', 'general_average' => 91.0]);
        $this->enroll('Male', ['promotion_status' => 'retained', 'general_average' => 72.0]);
        $this->enroll('Female', ['promotion_status' => 'promoted', 'general_average' => 86.5]);
        $this->enroll('Female', ['promotion_status' => 'promoted', 'is_irregular' => true, 'general_average' => 77.0]);

        $summary = app(Sf5ReportService::class)->build($this->section)['summary'];

        $this->assertSame(['male' => 1, 'female' => 1, 'total' => 2], $summary['actions']['PROMOTED']);
        $this->assertSame(['male' => 1, 'female' => 0, 'total' => 1], $summary['actions']['RETAINED']);
        $this->assertSame(['male' => 0, 'female' => 1, 'total' => 1], $summary['actions']['*IRREGULAR']);

        $this->assertSame(1, $summary['proficiency']['A']['male']);      // 91
        $this->assertSame(1, $summary['proficiency']['B']['male']);      // 72
        $this->assertSame(1, $summary['proficiency']['P']['female']);    // 86.5
        $this->assertSame(1, $summary['proficiency']['D']['female']);    // 77
        $this->assertSame(0, $summary['proficiency']['AP']['total']);
    }

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    */

    public function test_adviser_can_generate_the_sf5_pdf(): void
    {
        $this->enroll('Male', ['promotion_status' => 'promoted', 'general_average' => 88.25],
            ['last_name' => 'Dela Cruz', 'first_name' => 'Juan']);
        $this->enroll('Female', ['promotion_status' => 'promoted', 'general_average' => 92.125]);

        $response = $this->actingAs($this->adviser)
            ->get(route('reports.sf5.show', $this->section).'?head=MARIA+SANTOS&reviewer=PEDRO+REYES');

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_non_adviser_cannot_generate_or_enter_grades(): void
    {
        $other = User::factory()->create(['role' => 'teacher', 'is_active' => true, 'status' => User::STATUS_APPROVED]);
        Teacher::factory()->create(['user_id' => $other->id]);

        $this->actingAs($other)->get(route('reports.sf5.show', $this->section))->assertForbidden();
        $this->actingAs($other)->get(route('reports.sf5.grades', $this->section))->assertForbidden();
        $this->actingAs($other)->post(route('reports.sf5.grades.save', $this->section), ['rows' => []])
            ->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | Grades entry
    |--------------------------------------------------------------------------
    */

    public function test_adviser_can_bulk_save_averages(): void
    {
        $a = $this->enroll('Male', [], ['last_name' => 'Reyes', 'first_name' => 'Ana']);
        $b = $this->enroll('Female');

        $this->actingAs($this->adviser)->post(route('reports.sf5.grades.save', $this->section), [
            'rows' => [
                ['enrollment_id' => $a->id, 'general_average' => 88.25, 'is_irregular' => 0,
                 'subjects_completed' => '', 'subjects_incomplete' => 'Filipino 8'],
                ['enrollment_id' => $b->id, 'general_average' => 92.125, 'is_irregular' => 1],
            ],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame('88.250', (string) $a->refresh()->general_average);
        $this->assertSame('Filipino 8', $a->subjects_incomplete);
        $this->assertTrue($b->refresh()->is_irregular);
        $this->assertDatabaseHas('audit_logs', ['action' => 'sf5_grades_saved']);
    }

    public function test_an_out_of_range_average_is_rejected(): void
    {
        $a = $this->enroll('Male');

        $this->actingAs($this->adviser)->post(route('reports.sf5.grades.save', $this->section), [
            'rows' => [['enrollment_id' => $a->id, 'general_average' => 105]],
        ])->assertSessionHasErrors('rows.0.general_average');

        $this->assertNull($a->refresh()->general_average);
    }

    public function test_a_row_for_another_sections_learner_is_ignored(): void
    {
        $otherSection = Section::factory()->create([
            'school_year_id' => $this->year->id,
            'grade_level_id' => $this->section->grade_level_id,
        ]);
        $foreign = StudentEnrollment::create([
            'student_id' => Student::factory()->create(['gender' => 'Male'])->id,
            'school_year_id' => $this->year->id,
            'grade_level_id' => $otherSection->grade_level_id,
            'section_id' => $otherSection->id,
            'status' => 'enrolled', 'promotion_status' => 'pending', 'enrollment_date' => '2025-06-01',
        ]);

        $this->actingAs($this->adviser)->post(route('reports.sf5.grades.save', $this->section), [
            'rows' => [['enrollment_id' => $foreign->id, 'general_average' => 99]],
        ])->assertRedirect();

        $this->assertNull($foreign->refresh()->general_average);
    }

    public function test_the_grades_screen_renders_the_roster(): void
    {
        $this->enroll('Male', ['general_average' => 85.5], ['last_name' => 'Reyes', 'first_name' => 'Ana']);

        $this->actingAs($this->adviser)->get(route('reports.sf5.grades', $this->section))
            ->assertOk()
            ->assertSee('Reyes, Ana')
            ->assertSee('General average')
            ->assertSee('Generate SF5');
    }
}
