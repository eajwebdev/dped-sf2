<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\GradeLevel;
use App\Models\SchoolCalendar;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Services\SchoolCalendarService;
use App\Services\Sf2ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sf2ReportTest extends TestCase
{
    use RefreshDatabase;

    private SchoolYear $year;

    private Section $section;

    protected function setUp(): void
    {
        parent::setUp();
        $this->year = SchoolYear::factory()->active()->create([
            'start_date' => '2025-06-01', 'end_date' => '2026-03-31',
        ]);
        $grade = GradeLevel::factory()->create(['level_order' => 7, 'code' => 'G7']);
        app(SchoolCalendarService::class)->generate($this->year);
        $this->section = Section::factory()->create([
            'school_year_id' => $this->year->id, 'grade_level_id' => $grade->id,
        ]);
    }

    private function enroll(string $gender): StudentEnrollment
    {
        $student = Student::factory()->create(['gender' => $gender]);

        return StudentEnrollment::create([
            'student_id' => $student->id, 'school_year_id' => $this->year->id,
            'grade_level_id' => $this->section->grade_level_id, 'section_id' => $this->section->id,
            'status' => 'enrolled', 'promotion_status' => 'pending', 'enrollment_date' => '2025-06-01',
        ]);
    }

    private function mark(StudentEnrollment $e, string $date, string $status): void
    {
        Attendance::create([
            'student_enrollment_id' => $e->id, 'student_id' => $e->student_id,
            'section_id' => $this->section->id, 'school_year_id' => $this->year->id,
            'attendance_date' => $date, 'status' => $status,
        ]);
    }

    public function test_report_totals_are_computed_correctly(): void
    {
        $m1 = $this->enroll('Male');
        $m2 = $this->enroll('Male');
        $f1 = $this->enroll('Female');

        $day = SchoolCalendar::where('school_year_id', $this->year->id)
            ->where('is_class_day', true)->whereYear('date', 2025)->whereMonth('date', 6)
            ->orderBy('date')->value('date')->toDateString();

        $this->mark($m1, $day, 'present');
        $this->mark($m2, $day, 'absent');
        $this->mark($f1, $day, 'late'); // late counts as attended

        $data = app(Sf2ReportService::class)->build($this->section, 2025, 6);

        // Enrolment split.
        $this->assertSame(['male' => 2, 'female' => 1, 'total' => 3], $data['summary']['enrolment']);

        // Daily totals: present males = 1 (m1), present females = 1 (f1 late).
        $this->assertSame(1, $data['dailyTotals'][$day]['male']);
        $this->assertSame(1, $data['dailyTotals'][$day]['female']);
        $this->assertSame(2, $data['dailyTotals'][$day]['combined']);

        // Per-learner tallies.
        $m2Row = collect($data['males'])->firstWhere('enrollment_id', $m2->id);
        $this->assertSame(1, $m2Row['absent']);
        $f1Row = collect($data['females'])->firstWhere('enrollment_id', $f1->id);
        $this->assertSame(1, $f1Row['tardy']);
    }

    public function test_preview_renders_with_learner_and_totals(): void
    {
        $e = $this->enroll('Male');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('reports.sf2.show', ['section' => $this->section, 'year' => 2025, 'month' => 6]))
            ->assertOk()
            ->assertSee($e->student->last_name)
            ->assertSee('Daily Attendance Report of Learners');
    }

    public function test_pdf_export_returns_a_pdf(): void
    {
        $this->enroll('Male');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)
            ->get(route('reports.sf2.pdf', ['section' => $this->section, 'year' => 2025, 'month' => 6]));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_excel_export_downloads(): void
    {
        $this->enroll('Female');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)
            ->get(route('reports.sf2.excel', ['section' => $this->section, 'year' => 2025, 'month' => 6]));

        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml', $response->headers->get('content-type'));
    }

    public function test_unassigned_teacher_cannot_open_report(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $this->actingAs($teacher)
            ->get(route('reports.sf2.show', ['section' => $this->section, 'year' => 2025, 'month' => 6]))
            ->assertForbidden();
    }
}
