<?php

namespace Tests\Feature;

use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\TeacherSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherScheduleTest extends TestCase
{
    use RefreshDatabase;

    private SchoolYear $year;

    private Section $section;

    private User $teacherUser;

    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->year = SchoolYear::factory()->active()->create();
        $grade = GradeLevel::factory()->create();

        $this->teacherUser = User::factory()->create(['role' => User::ROLE_TEACHER, 'is_active' => true]);
        $this->teacher = Teacher::factory()->create(['user_id' => $this->teacherUser->id]);

        $this->section = Section::factory()->create([
            'school_year_id' => $this->year->id,
            'grade_level_id' => $grade->id,
            'adviser_id' => $this->teacher->id,
        ]);
    }

    public function test_teacher_can_view_schedule_page(): void
    {
        $this->actingAs($this->teacherUser)
            ->get(route('schedule.index'))
            ->assertOk()
            ->assertSee('Add Class');
    }

    public function test_teacher_can_create_a_schedule_entry(): void
    {
        $this->actingAs($this->teacherUser)->post(route('schedule.store'), [
            'section_id' => $this->section->id,
            'day_of_week' => 1,
            'start_time' => '07:30',
            'end_time' => '08:30',
            'room' => '204',
            'color' => 'indigo',
        ])->assertRedirect();

        $this->assertDatabaseHas('teacher_schedules', [
            'teacher_id' => $this->teacher->id,
            'section_id' => $this->section->id,
            'day_of_week' => 1,
        ]);
    }

    public function test_overlapping_schedule_is_rejected(): void
    {
        TeacherSchedule::create([
            'teacher_id' => $this->teacher->id,
            'school_year_id' => $this->year->id,
            'section_id' => $this->section->id,
            'day_of_week' => 1,
            'start_time' => '07:00',
            'end_time' => '08:00',
        ]);

        $this->actingAs($this->teacherUser)->post(route('schedule.store'), [
            'section_id' => $this->section->id,
            'day_of_week' => 1,
            'start_time' => '07:30',
            'end_time' => '08:30',
        ])->assertSessionHasErrors('start_time');

        $this->assertSame(1, TeacherSchedule::count());
    }

    public function test_teacher_cannot_touch_another_teachers_schedule(): void
    {
        $other = Teacher::factory()->create(['user_id' => User::factory()->create(['role' => User::ROLE_TEACHER])->id]);
        $entry = TeacherSchedule::create([
            'teacher_id' => $other->id,
            'school_year_id' => $this->year->id,
            'section_id' => $this->section->id,
            'day_of_week' => 2,
            'start_time' => '09:00',
            'end_time' => '10:00',
        ]);

        $this->actingAs($this->teacherUser)
            ->delete(route('schedule.destroy', $entry))
            ->assertForbidden();
    }

    public function test_teacher_cannot_schedule_in_an_inaccessible_section(): void
    {
        $foreign = Section::factory()->create([
            'school_year_id' => $this->year->id,
            'grade_level_id' => GradeLevel::factory()->create()->id,
        ]);

        $this->actingAs($this->teacherUser)->post(route('schedule.store'), [
            'section_id' => $foreign->id,
            'day_of_week' => 1,
            'start_time' => '07:00',
            'end_time' => '08:00',
        ])->assertSessionHasErrors('section_id');
    }

    /**
     * The scan portal is key-gated rather than login-gated: /portal now lands
     * on class-key entry, so the class key is the credential and no schedule
     * lookup happens up front.
     */
    public function test_scan_portal_redirects_to_class_key_entry(): void
    {
        $this->actingAs($this->teacherUser)
            ->get(route('portal'))
            ->assertRedirect('/class-scan');
    }

    public function test_class_key_entry_page_loads_without_login(): void
    {
        $this->get(route('class-scan.enter'))->assertOk();
    }

    public function test_qr_cards_zip_downloads_for_authorized_teacher(): void
    {
        $student = Student::factory()->create();
        StudentEnrollment::create([
            'student_id' => $student->id,
            'school_year_id' => $this->year->id,
            'grade_level_id' => $this->section->grade_level_id,
            'section_id' => $this->section->id,
            'status' => 'enrolled',
            'enrollment_date' => now()->toDateString(),
        ]);

        $this->actingAs($this->teacherUser)
            ->get(route('qr-cards.section', $this->section))
            ->assertOk()
            ->assertHeader('content-type', 'application/zip');
    }

    public function test_qr_cards_forbidden_for_unrelated_teacher(): void
    {
        $strangerUser = User::factory()->create(['role' => User::ROLE_TEACHER, 'is_active' => true]);
        Teacher::factory()->create(['user_id' => $strangerUser->id]);

        $this->actingAs($strangerUser)
            ->get(route('qr-cards.section', $this->section))
            ->assertForbidden();
    }
}
