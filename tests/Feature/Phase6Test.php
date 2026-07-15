<?php

namespace Tests\Feature;

use App\Models\AttendanceSetting;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\User;
use App\Services\SchoolCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class Phase6Test extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);
    }

    public function test_promotion_creates_next_year_enrollments_and_preserves_history(): void
    {
        $from = SchoolYear::factory()->active()->create(['name' => '2025-2026']);
        $to = SchoolYear::factory()->create(['name' => '2026-2027', 'start_date' => '2026-06-01', 'end_date' => '2027-03-31']);
        $g7 = GradeLevel::factory()->create(['level_order' => 7, 'code' => 'G7', 'name' => 'Grade 7']);
        $g8 = GradeLevel::factory()->create(['level_order' => 8, 'code' => 'G8', 'name' => 'Grade 8']);

        $srcSection = Section::factory()->create(['school_year_id' => $from->id, 'grade_level_id' => $g7->id]);
        $dstSection = Section::factory()->create(['school_year_id' => $to->id, 'grade_level_id' => $g8->id]);

        $students = Student::factory()->count(2)->create();
        foreach ($students as $s) {
            StudentEnrollment::create([
                'student_id' => $s->id, 'school_year_id' => $from->id, 'grade_level_id' => $g7->id,
                'section_id' => $srcSection->id, 'status' => 'enrolled', 'promotion_status' => 'pending', 'enrollment_date' => '2025-06-01',
            ]);
        }

        $this->actingAs($this->admin())->post(route('admin.promotion.promote'), [
            'from_year_id' => $from->id, 'to_year_id' => $to->id,
            'section_map' => [$srcSection->id => $dstSection->id],
        ])->assertRedirect();

        // New enrollments in the target year, Grade 8.
        $this->assertSame(2, StudentEnrollment::where('school_year_id', $to->id)->where('grade_level_id', $g8->id)->count());
        // Original enrollments still exist (history preserved) and are marked promoted.
        $this->assertSame(2, StudentEnrollment::where('school_year_id', $from->id)->where('promotion_status', 'promoted')->count());
        // Every student now has exactly two enrollments (one per year).
        $this->assertSame(2, StudentEnrollment::where('student_id', $students->first()->id)->count());
    }

    public function test_graduating_grade_learners_are_marked_graduated(): void
    {
        $from = SchoolYear::factory()->active()->create();
        $to = SchoolYear::factory()->create(['start_date' => '2026-06-01', 'end_date' => '2027-03-31']);
        $g12 = GradeLevel::factory()->create(['level_order' => 12, 'code' => 'G12', 'is_graduating' => true]);
        $section = Section::factory()->create(['school_year_id' => $from->id, 'grade_level_id' => $g12->id]);
        $student = Student::factory()->create();
        StudentEnrollment::create([
            'student_id' => $student->id, 'school_year_id' => $from->id, 'grade_level_id' => $g12->id,
            'section_id' => $section->id, 'status' => 'enrolled', 'promotion_status' => 'pending', 'enrollment_date' => now(),
        ]);

        $this->actingAs($this->admin())->post(route('admin.promotion.promote'), [
            'from_year_id' => $from->id, 'to_year_id' => $to->id, 'section_map' => [],
        ])->assertRedirect();

        $this->assertSame('graduated', $student->fresh()->status);
        $this->assertSame(0, StudentEnrollment::where('school_year_id', $to->id)->count());
    }

    public function test_admin_dashboard_and_audit_and_search_pages_render(): void
    {
        SchoolYear::factory()->active()->create();
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('admin.audit-logs.index'))->assertOk();

        $student = Student::factory()->create(['last_name' => 'Zamora']);
        $this->actingAs($admin)->get(route('admin.search.index', ['q' => 'Zamora']))
            ->assertOk()->assertSee('Zamora');
    }

    public function test_student_import_creates_students(): void
    {
        $csv = "lrn,first_name,middle_name,last_name,suffix,gender,birthdate,address,guardian_name,guardian_contact\n"
            ."111122223333,Ana,Cruz,Santos,,Female,2013-01-15,Sample,Maria,09170000000\n"
            ."444455556666,Ben,Lopez,Reyes,,Male,2012-11-02,Sample,Jose,09171111111\n";

        $file = UploadedFile::fake()->createWithContent('students.csv', $csv);

        $this->actingAs($this->admin())->post(route('admin.students.import'), ['file' => $file])->assertRedirect();

        $this->assertDatabaseHas('students', ['lrn' => '111122223333', 'last_name' => 'Santos']);
        $this->assertDatabaseHas('students', ['lrn' => '444455556666', 'last_name' => 'Reyes']);
    }

    public function test_student_export_downloads(): void
    {
        Student::factory()->count(2)->create();
        $response = $this->actingAs($this->admin())->get(route('admin.students.export'));
        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml', $response->headers->get('content-type'));
    }

    public function test_qr_check_in_marks_a_learner_present(): void
    {
        AttendanceSetting::create(['school_year_id' => null, 'edit_lock_days' => 7]);
        $year = SchoolYear::factory()->active()->create(['start_date' => now()->subMonth()->toDateString(), 'end_date' => now()->addMonths(6)->toDateString()]);
        app(SchoolCalendarService::class)->generate($year);
        $grade = GradeLevel::factory()->create(['level_order' => 7, 'code' => 'G7']);
        $section = Section::factory()->create(['school_year_id' => $year->id, 'grade_level_id' => $grade->id]);
        $student = Student::factory()->create(['qr_token' => 'known-token-123']);
        StudentEnrollment::create([
            'student_id' => $student->id, 'school_year_id' => $year->id, 'grade_level_id' => $grade->id,
            'section_id' => $section->id, 'status' => 'enrolled', 'promotion_status' => 'pending', 'enrollment_date' => now(),
        ]);

        // Use a recent class day (weekday) within the window.
        $day = now();
        while ($day->isWeekend()) {
            $day->subDay();
        }

        $this->actingAs($this->admin())->postJson(route('attendance.checkin', $section), [
            'token' => 'known-token-123', 'date' => $day->toDateString(),
        ])->assertOk()->assertJsonPath('ok', true);

        $this->assertDatabaseHas('attendance', ['student_id' => $student->id, 'status' => 'present']);

        // Unknown token → 404.
        $this->actingAs($this->admin())->postJson(route('attendance.checkin', $section), [
            'token' => 'nope', 'date' => $day->toDateString(),
        ])->assertStatus(404);
    }
}
