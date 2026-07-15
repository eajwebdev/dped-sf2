<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\SchoolCalendar;
use App\Models\SchoolYear;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SchoolCalendarService
{
    /**
     * (Re)generate the per-day calendar for a school year from its date range,
     * marking weekends and holidays as non-class days. Admin overrides
     * (is_override = true) are preserved across regeneration.
     */
    public function generate(SchoolYear $schoolYear): int
    {
        $overrides = SchoolCalendar::query()
            ->where('school_year_id', $schoolYear->id)
            ->where('is_override', true)
            ->get()
            ->keyBy(fn ($row) => $row->date->toDateString());

        $holidays = $this->holidayMap($schoolYear);

        $rows = [];
        $now = now();
        $period = CarbonPeriod::create($schoolYear->start_date, $schoolYear->end_date);

        foreach ($period as $date) {
            /** @var Carbon $date */
            $key = $date->toDateString();

            if ($overrides->has($key)) {
                continue; // leave admin-set day untouched
            }

            [$dayType, $isClassDay, $remarks] = $this->classify($date, $holidays);

            $rows[] = [
                'school_year_id' => $schoolYear->id,
                'date' => $key,
                'day_type' => $dayType,
                'is_class_day' => $isClassDay,
                'is_override' => false,
                'remarks' => $remarks,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::transaction(function () use ($schoolYear, $rows) {
            SchoolCalendar::query()
                ->where('school_year_id', $schoolYear->id)
                ->where('is_override', false)
                ->delete();

            foreach (array_chunk($rows, 500) as $chunk) {
                SchoolCalendar::insert($chunk);
            }
        });

        return count($rows) + $overrides->count();
    }

    /**
     * Count class days within an (optional) date window for a school year.
     * Used for SF2 "No. of Days of Classes" and monthly attendance percentages.
     */
    public function classDayCount(SchoolYear $schoolYear, ?Carbon $from = null, ?Carbon $to = null): int
    {
        return SchoolCalendar::query()
            ->where('school_year_id', $schoolYear->id)
            ->where('is_class_day', true)
            ->when($from, fn ($q) => $q->whereDate('date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('date', '<=', $to))
            ->count();
    }

    /** The ordered list of class-day dates in a window (for building the SF2 grid). */
    public function classDays(SchoolYear $schoolYear, Carbon $from, Carbon $to): Collection
    {
        return SchoolCalendar::query()
            ->where('school_year_id', $schoolYear->id)
            ->where('is_class_day', true)
            ->whereDate('date', '>=', $from)
            ->whereDate('date', '<=', $to)
            ->orderBy('date')
            ->pluck('date');
    }

    public function isClassDay(SchoolYear $schoolYear, Carbon $date): bool
    {
        return SchoolCalendar::query()
            ->where('school_year_id', $schoolYear->id)
            ->whereDate('date', $date)
            ->where('is_class_day', true)
            ->exists();
    }

    /** @return array<string, Holiday> keyed by date string */
    protected function holidayMap(SchoolYear $schoolYear): array
    {
        return Holiday::query()
            ->where(function ($q) use ($schoolYear) {
                $q->whereNull('school_year_id')->orWhere('school_year_id', $schoolYear->id);
            })
            ->get()
            ->keyBy(fn (Holiday $h) => $h->date->toDateString())
            ->all();
    }

    /**
     * @param  array<string, Holiday>  $holidays
     * @return array{0:string,1:bool,2:?string} [day_type, is_class_day, remarks]
     */
    protected function classify(Carbon $date, array $holidays): array
    {
        if ($date->isWeekend()) {
            return ['weekend', false, null];
        }

        $key = $date->toDateString();
        if (isset($holidays[$key])) {
            $holiday = $holidays[$key];

            return [$this->holidayDayType($holiday->type), false, $holiday->name];
        }

        return ['school_day', true, null];
    }

    protected function holidayDayType(string $holidayType): string
    {
        return match ($holidayType) {
            'suspension' => 'suspension',
            'no_class' => 'no_class',
            default => 'holiday',
        };
    }
}
