<?php

namespace Tests\Unit;

use App\Models\Holiday;
use App\Models\SchoolCalendar;
use App\Models\SchoolYear;
use App\Services\SchoolCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SchoolCalendarServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): SchoolCalendarService
    {
        return app(SchoolCalendarService::class);
    }

    public function test_generate_excludes_weekends(): void
    {
        // Mon 2025-06-02 .. Fri 2025-06-13 → 10 weekday class days across two weeks.
        $sy = SchoolYear::factory()->create(['start_date' => '2025-06-02', 'end_date' => '2025-06-13']);
        $this->service()->generate($sy);

        $this->assertSame(10, $this->service()->classDayCount($sy));
        $this->assertSame(2, $sy->calendarDays()->where('is_class_day', false)->count()); // one weekend (Sat + Sun)
    }

    public function test_holiday_removes_a_class_day(): void
    {
        $sy = SchoolYear::factory()->create(['start_date' => '2025-06-02', 'end_date' => '2025-06-13']);
        Holiday::create(['school_year_id' => $sy->id, 'date' => '2025-06-12', 'name' => 'Independence Day', 'type' => 'holiday']);

        $this->service()->generate($sy);

        $this->assertSame(9, $this->service()->classDayCount($sy));
        $this->assertFalse($this->service()->isClassDay($sy, Carbon::parse('2025-06-12')));
        $this->assertTrue($this->service()->isClassDay($sy, Carbon::parse('2025-06-11')));
    }

    public function test_class_day_count_respects_date_window(): void
    {
        $sy = SchoolYear::factory()->create(['start_date' => '2025-06-02', 'end_date' => '2025-06-13']);
        $this->service()->generate($sy);

        // Only the first week (Mon–Fri) = 5 class days.
        $count = $this->service()->classDayCount($sy, Carbon::parse('2025-06-02'), Carbon::parse('2025-06-06'));
        $this->assertSame(5, $count);
    }

    public function test_admin_overrides_survive_regeneration(): void
    {
        $sy = SchoolYear::factory()->create(['start_date' => '2025-06-02', 'end_date' => '2025-06-13']);
        $this->service()->generate($sy);

        // Force a Saturday to be a class day (make-up day).
        SchoolCalendar::updateOrCreate(
            ['school_year_id' => $sy->id, 'date' => '2025-06-07'],
            ['day_type' => 'school_day', 'is_class_day' => true, 'is_override' => true],
        );

        $this->service()->generate($sy); // regenerate

        $saturday = SchoolCalendar::where('school_year_id', $sy->id)->whereDate('date', '2025-06-07')->first();
        $this->assertTrue($saturday->is_class_day, 'Admin override should be preserved on regeneration');
    }
}
