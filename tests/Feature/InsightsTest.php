<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\GradeLevel;
use App\Models\SchoolCalendar;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\User;
use App\Services\InsightsService;
use App\Services\SchoolCalendarService;
use App\Support\SubscriptionPlans;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class InsightsTest extends TestCase
{
    use RefreshDatabase;

    private SchoolYear $year;

    private Section $section;

    private User $adviser;

    protected function setUp(): void
    {
        parent::setUp();

        // A school year that is already in progress relative to "today".
        $this->year = SchoolYear::factory()->active()->create([
            'start_date' => Carbon::today()->subMonths(2)->startOfMonth(),
            'end_date' => Carbon::today()->addMonths(4),
        ]);
        app(SchoolCalendarService::class)->generate($this->year);

        $grade = GradeLevel::factory()->create(['level_order' => 8, 'code' => 'G8']);

        // A Professional subscriber, since insights are plan-gated.
        $this->adviser = User::factory()->create([
            'role' => 'teacher', 'is_active' => true, 'status' => User::STATUS_APPROVED,
            'subscription_plan' => SubscriptionPlans::PROFESSIONAL,
            'subscribed_until' => Carbon::today()->addMonths(3),
        ]);
        $teacher = Teacher::factory()->create(['user_id' => $this->adviser->id]);

        $this->section = Section::factory()->create([
            'school_year_id' => $this->year->id,
            'grade_level_id' => $grade->id,
            'adviser_id' => $teacher->id,
        ]);
    }

    private function enroll(string $gender, array $student = []): StudentEnrollment
    {
        $s = Student::factory()->create($student + ['gender' => $gender]);

        return StudentEnrollment::create([
            'student_id' => $s->id,
            'school_year_id' => $this->year->id,
            'grade_level_id' => $this->section->grade_level_id,
            'section_id' => $this->section->id,
            'status' => 'enrolled',
            'promotion_status' => 'pending',
            'enrollment_date' => $this->year->start_date->toDateString(),
        ]);
    }

    /** Mark a learner's status on the N most recent class days (oldest first). */
    private function markRecent(StudentEnrollment $e, array $statuses): void
    {
        $days = SchoolCalendar::where('school_year_id', $this->year->id)
            ->where('is_class_day', true)
            ->whereDate('date', '<=', Carbon::today())
            ->orderByDesc('date')
            ->take(count($statuses))
            ->pluck('date')
            ->reverse()
            ->values();

        foreach ($days as $i => $date) {
            Attendance::create([
                'student_enrollment_id' => $e->id, 'student_id' => $e->student_id,
                'section_id' => $this->section->id, 'school_year_id' => $this->year->id,
                'attendance_date' => $date, 'status' => $statuses[$i],
            ]);
        }
    }

    public function test_a_current_absence_streak_lands_on_the_watchlist(): void
    {
        $slipping = $this->enroll('Male', ['last_name' => 'Aquino']);
        $fine = $this->enroll('Male', ['last_name' => 'Bautista']);

        $this->markRecent($slipping, ['present', 'absent', 'absent', 'absent']);
        $this->markRecent($fine, ['present', 'present', 'present', 'present']);

        $data = app(InsightsService::class)->build($this->adviser, $this->section);

        $this->assertCount(1, $data['watchlist']);
        $this->assertStringStartsWith('Aquino', $data['watchlist'][0]['name']);
        $this->assertSame(3, $data['watchlist'][0]['streak']);
        $this->assertFalse($data['watchlist'][0]['critical']);
    }

    public function test_five_straight_absences_are_critical_and_sorted_first(): void
    {
        $critical = $this->enroll('Female', ['last_name' => 'Cruz']);
        $warning = $this->enroll('Male', ['last_name' => 'Diaz']);

        $this->markRecent($critical, ['absent', 'absent', 'absent', 'absent', 'absent']);
        $this->markRecent($warning, ['present', 'absent', 'absent', 'absent']);

        $data = app(InsightsService::class)->build($this->adviser, $this->section);

        $this->assertTrue($data['watchlist'][0]['critical']);
        $this->assertStringStartsWith('Cruz', $data['watchlist'][0]['name']);
    }

    public function test_an_ended_streak_does_not_alarm(): void
    {
        $recovered = $this->enroll('Male');
        // Absent streak broken by two present days: not "slipping now".
        $this->markRecent($recovered, ['absent', 'absent', 'absent', 'present', 'present']);

        $data = app(InsightsService::class)->build($this->adviser, $this->section);

        $this->assertSame(0, $data['watchlist'][0]['streak'] ?? 0);
    }

    public function test_tardies_and_book_and_band_panels_aggregate(): void
    {
        $e = $this->enroll('Male', ['last_name' => 'Evangelista']);
        $this->markRecent($e, ['late', 'late', 'present']);
        $e->update(['general_average' => 92.5]);

        $book = \App\Models\Textbook::create([
            'section_id' => $this->section->id, 'subject_area' => 'Math', 'title' => 'Module',
        ]);
        \App\Models\TextbookIssuance::create([
            'textbook_id' => $book->id, 'student_enrollment_id' => $e->id, 'issued_at' => Carbon::today()->subMonth(),
        ]);

        $data = app(InsightsService::class)->build($this->adviser, $this->section);

        $this->assertSame(2, $data['tardiest'][0]['tardies']);
        $this->assertSame(1, $data['books']['issued']);
        $this->assertSame(1, $data['books']['outstanding']);
        $this->assertSame(1, $data['bands']['A']);
    }

    public function test_the_dashboard_renders_for_a_professional_adviser(): void
    {
        $e = $this->enroll('Male', ['last_name' => 'Flores', 'first_name' => 'Gino']);
        $this->markRecent($e, ['absent', 'absent', 'absent']);

        $this->actingAs($this->adviser)->get(route('insights.show', $this->section))
            ->assertOk()
            ->assertSee('Attendance watchlist')
            ->assertSee('Flores, Gino')
            ->assertSee('Monthly attendance');
    }

    public function test_a_starter_subscriber_is_gated_out(): void
    {
        $starter = User::factory()->create([
            'role' => 'teacher', 'is_active' => true, 'status' => User::STATUS_APPROVED,
            'subscription_plan' => SubscriptionPlans::STARTER,
            'subscribed_until' => Carbon::today()->addMonths(3),
        ]);

        $this->actingAs($starter)->get(route('insights.index'))
            ->assertRedirect(route('subscribe.show'))
            ->assertSessionHas('error');
    }

    public function test_a_non_adviser_professional_cannot_open_another_class(): void
    {
        $other = User::factory()->create([
            'role' => 'teacher', 'is_active' => true, 'status' => User::STATUS_APPROVED,
            'subscription_plan' => SubscriptionPlans::PROFESSIONAL,
            'subscribed_until' => Carbon::today()->addMonths(3),
        ]);
        Teacher::factory()->create(['user_id' => $other->id]);

        $this->actingAs($other)->get(route('insights.show', $this->section))->assertForbidden();
    }
}
