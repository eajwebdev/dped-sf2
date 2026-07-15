<?php

namespace Tests\Unit;

use App\Models\AttendanceSetting;
use App\Models\GradeLevel;
use App\Models\Holiday;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\SchoolCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private SchoolYear $year;

    private Section $section;

    protected function setUp(): void
    {
        parent::setUp();
        AttendanceSetting::create(['school_year_id' => null, 'edit_lock_days' => 7]);
        $this->year = SchoolYear::factory()->active()->create([
            'start_date' => Carbon::today()->subMonth()->toDateString(),
            'end_date' => Carbon::today()->addMonths(6)->toDateString(),
        ]);
        $grade = GradeLevel::factory()->create(['level_order' => 7, 'code' => 'G7']);
        app(SchoolCalendarService::class)->generate($this->year);
        $this->section = Section::factory()->create([
            'school_year_id' => $this->year->id, 'grade_level_id' => $grade->id,
        ]);
    }

    private function teacher(): User
    {
        return User::factory()->create(['role' => User::ROLE_TEACHER]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function recentWeekday(): Carbon
    {
        $d = Carbon::today();
        while ($d->isWeekend()) {
            $d->subDay();
        }

        return $d;
    }

    public function test_future_date_is_not_editable_for_teacher(): void
    {
        $service = app(AttendanceService::class);
        $edit = $service->editability($this->teacher(), $this->section, Carbon::tomorrow());

        $this->assertFalse($edit['editable']);
        $this->assertSame('future', $edit['reason']);
    }

    public function test_holiday_is_not_editable_for_teacher(): void
    {
        $day = $this->recentWeekday();
        Holiday::create(['school_year_id' => $this->year->id, 'date' => $day->toDateString(), 'name' => 'Special Holiday', 'type' => 'holiday']);
        app(SchoolCalendarService::class)->generate($this->year); // rebuild so the day is non-class

        $edit = app(AttendanceService::class)->editability($this->teacher(), $this->section, $day);

        $this->assertFalse($edit['editable']);
        $this->assertSame('holiday', $edit['reason']);
    }

    public function test_locked_past_date_is_editable_for_admin(): void
    {
        $old = Carbon::today()->subDays(20);
        while ($old->isWeekend()) {
            $old->subDay();
        }

        $teacherEdit = app(AttendanceService::class)->editability($this->teacher(), $this->section, $old);
        $adminEdit = app(AttendanceService::class)->editability($this->admin(), $this->section, $old);

        $this->assertFalse($teacherEdit['editable']);
        $this->assertSame('locked', $teacherEdit['reason']);
        $this->assertTrue($adminEdit['editable'], 'Admins bypass the edit-lock window');
    }

    public function test_recent_class_day_is_editable(): void
    {
        $edit = app(AttendanceService::class)->editability($this->teacher(), $this->section, $this->recentWeekday());
        $this->assertTrue($edit['editable']);
        $this->assertNull($edit['reason']);
    }
}
