<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\GradeLevel;
use App\Models\SchoolCalendar;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    private SchoolYear $year;

    private GradeLevel $grade;

    protected function setUp(): void
    {
        parent::setUp();
        AttendanceSetting::create(['school_year_id' => null, 'edit_lock_days' => 7, 'autosave_seconds' => 15]);
        $this->year = SchoolYear::factory()->active()->create([
            'start_date' => Carbon::today()->subMonths(2)->toDateString(),
            'end_date' => Carbon::today()->addMonths(6)->toDateString(),
        ]);
        $this->grade = GradeLevel::factory()->create(['level_order' => 7, 'code' => 'G7']);
        app(SchoolCalendarService::class)->generate($this->year);
    }

    private function sectionWithTeacher(bool $asAdviser = true): array
    {
        $teacherUser = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $teacher = Teacher::factory()->create(['user_id' => $teacherUser->id]);
        $section = Section::factory()->create([
            'school_year_id' => $this->year->id,
            'grade_level_id' => $this->grade->id,
            'adviser_id' => $asAdviser ? $teacher->id : null,
        ]);

        if (! $asAdviser) {
            $subject = Subject::factory()->create();
            $sa = SubjectAssignment::create([
                'school_year_id' => $this->year->id, 'grade_level_id' => $this->grade->id,
                'section_id' => $section->id, 'subject_id' => $subject->id,
            ]);
            TeacherSubjectAssignment::create(['subject_assignment_id' => $sa->id, 'teacher_id' => $teacher->id]);
        }

        return [$teacherUser, $teacher, $section];
    }

    private function enroll(Section $section, int $count = 3)
    {
        return Student::factory()->count($count)->create()->map(fn ($s) => StudentEnrollment::create([
            'student_id' => $s->id, 'school_year_id' => $section->school_year_id,
            'grade_level_id' => $section->grade_level_id, 'section_id' => $section->id,
            'status' => 'enrolled', 'promotion_status' => 'pending', 'enrollment_date' => Carbon::today(),
        ]));
    }

    /** Pick a class day within the last week so the edit window is open. */
    private function recentClassDay(): Carbon
    {
        return Carbon::parse(SchoolCalendar::where('school_year_id', $this->year->id)
            ->where('is_class_day', true)
            ->whereDate('date', '<=', Carbon::today())
            ->whereDate('date', '>=', Carbon::today()->subDays(6))
            ->orderByDesc('date')->value('date'));
    }

    public function test_teacher_only_sees_their_own_sections(): void
    {
        [$mineUser,, $mine] = $this->sectionWithTeacher();
        [, , $other] = $this->sectionWithTeacher();

        $this->actingAs($mineUser)->get(route('attendance.index'))
            ->assertOk()
            ->assertSee($mine->name)
            ->assertDontSee($other->name);
    }

    public function test_teacher_cannot_open_a_section_they_are_not_assigned_to(): void
    {
        [$mineUser] = $this->sectionWithTeacher();
        [, , $other] = $this->sectionWithTeacher();

        $this->actingAs($mineUser)->get(route('attendance.sheet', $other))->assertForbidden();
    }

    public function test_teacher_can_save_attendance_and_it_logs(): void
    {
        [$user,, $section] = $this->sectionWithTeacher();
        $enrollments = $this->enroll($section, 3);
        $date = $this->recentClassDay();

        $marks = $enrollments->map(fn ($e, $i) => [
            'enrollment_id' => $e->id,
            'status' => $i === 0 ? 'absent' : 'present',
            'remarks' => null,
        ])->values()->all();

        $this->actingAs($user)->postJson(route('attendance.save', $section), [
            'date' => $date->toDateString(), 'marks' => $marks,
        ])->assertOk()->assertJsonPath('saved', 3);

        $this->assertSame(3, Attendance::where('section_id', $section->id)->count());
        $this->assertDatabaseHas('attendance_logs', ['action' => 'created', 'new_status' => 'absent']);
    }

    public function test_updating_a_mark_records_a_log_with_old_and_new(): void
    {
        [$user,, $section] = $this->sectionWithTeacher();
        $e = $this->enroll($section, 1)->first();
        $date = $this->recentClassDay();

        $this->actingAs($user)->postJson(route('attendance.save', $section), [
            'date' => $date->toDateString(), 'marks' => [['enrollment_id' => $e->id, 'status' => 'present']],
        ])->assertOk();

        $this->actingAs($user)->postJson(route('attendance.save', $section), [
            'date' => $date->toDateString(), 'marks' => [['enrollment_id' => $e->id, 'status' => 'late']],
        ])->assertOk();

        $this->assertDatabaseHas('attendance_logs', ['action' => 'updated', 'old_status' => 'present', 'new_status' => 'late']);
        $this->assertSame('late', Attendance::first()->status);
    }

    public function test_locked_past_date_is_rejected_for_teacher_but_admin_can_unlock(): void
    {
        [$user,, $section] = $this->sectionWithTeacher();
        $e = $this->enroll($section, 1)->first();

        // A class day older than the 7-day window.
        $oldDate = Carbon::parse(SchoolCalendar::where('school_year_id', $this->year->id)
            ->where('is_class_day', true)
            ->whereDate('date', '<', Carbon::today()->subDays(10))
            ->orderByDesc('date')->value('date'));

        $this->actingAs($user)->postJson(route('attendance.save', $section), [
            'date' => $oldDate->toDateString(), 'marks' => [['enrollment_id' => $e->id, 'status' => 'present']],
        ])->assertStatus(422);
        $this->assertSame(0, Attendance::count());

        // Admin unlocks the date, then the teacher can save.
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin)->post(route('attendance.unlock', $section), ['date' => $oldDate->toDateString()])->assertRedirect();

        $this->actingAs($user)->postJson(route('attendance.save', $section), [
            'date' => $oldDate->toDateString(), 'marks' => [['enrollment_id' => $e->id, 'status' => 'present']],
        ])->assertOk();
        $this->assertSame(1, Attendance::count());
    }

    public function test_non_class_day_is_rejected_for_teacher(): void
    {
        [$user,, $section] = $this->sectionWithTeacher();
        $e = $this->enroll($section, 1)->first();

        // Find a weekend (non-class day) within the open window.
        $weekend = Carbon::today();
        while ($weekend->isWeekday() || $weekend->gt(Carbon::today())) {
            $weekend->subDay();
        }

        $this->actingAs($user)->postJson(route('attendance.save', $section), [
            'date' => $weekend->toDateString(), 'marks' => [['enrollment_id' => $e->id, 'status' => 'present']],
        ])->assertStatus(422);
        $this->assertSame(0, Attendance::count());
    }

    public function test_subject_teacher_can_access_section_even_without_being_adviser(): void
    {
        [$user,, $section] = $this->sectionWithTeacher(asAdviser: false);
        $this->actingAs($user)->get(route('attendance.sheet', $section))->assertOk();
    }
}
