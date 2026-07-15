<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use Illuminate\Support\Carbon;

class DashboardService
{
    private const ATTENDED = ['present', 'late', 'half_day'];

    public function __construct(private readonly SchoolCalendarService $calendar) {}

    public function adminData(): array
    {
        $sy = SchoolYear::current();

        return [
            'schoolYear' => $sy,
            'cards' => $this->cards($sy),
            'today' => $this->today($sy),
            'byGrade' => $this->enrollmentByGrade($sy),
            'trend' => $this->attendanceTrend($sy),
        ];
    }

    private function cards(?SchoolYear $sy): array
    {
        return [
            'students' => $sy ? StudentEnrollment::where('school_year_id', $sy->id)->distinct('student_id')->count('student_id') : 0,
            'teachers' => Teacher::where('is_active', true)->count(),
            'sections' => $sy ? Section::where('school_year_id', $sy->id)->count() : 0,
            'schoolYear' => $sy?->name ?? '—',
        ];
    }

    private function today(?SchoolYear $sy): array
    {
        $enrolled = $sy ? StudentEnrollment::where('school_year_id', $sy->id)
            ->whereIn('status', ['enrolled', 'transferred_in'])->count() : 0;

        $counts = $sy ? Attendance::where('school_year_id', $sy->id)
            ->whereDate('attendance_date', Carbon::today())
            ->selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status')
            : collect();

        $present = (int) ($counts['present'] ?? 0) + (int) ($counts['late'] ?? 0) + (int) ($counts['half_day'] ?? 0);
        $marked = (int) $counts->sum();

        return [
            'present' => $present,
            'absent' => (int) ($counts['absent'] ?? 0),
            'late' => (int) ($counts['late'] ?? 0),
            'enrolled' => $enrolled,
            'completion' => $enrolled > 0 ? round($marked / $enrolled * 100) : 0,
        ];
    }

    /** @return array<int, array{label:string, value:int}> */
    private function enrollmentByGrade(?SchoolYear $sy): array
    {
        if (! $sy) {
            return [];
        }

        $counts = StudentEnrollment::where('student_enrollments.school_year_id', $sy->id)
            ->whereIn('status', ['enrolled', 'transferred_in'])
            ->join('grade_levels', 'grade_levels.id', '=', 'student_enrollments.grade_level_id')
            ->selectRaw('grade_levels.name as label, grade_levels.level_order as ord, count(*) as value')
            ->groupBy('grade_levels.id', 'grade_levels.name', 'grade_levels.level_order')
            ->orderBy('ord')
            ->get();

        return $counts->map(fn ($r) => ['label' => $r->label, 'value' => (int) $r->value])->all();
    }

    /**
     * Attendance rate for the last few class days.
     *
     * @return array<int, array{label:string, rate:int, present:int}>
     */
    private function attendanceTrend(?SchoolYear $sy, int $days = 7): array
    {
        if (! $sy) {
            return [];
        }

        $enrolled = StudentEnrollment::where('school_year_id', $sy->id)
            ->whereIn('status', ['enrolled', 'transferred_in'])->count();

        $classDays = $this->calendar
            ->classDays($sy, $sy->start_date, Carbon::today()->min($sy->end_date))
            ->reverse()->take($days)->reverse()->values();

        $rows = [];
        foreach ($classDays as $day) {
            $date = Carbon::parse($day);
            $present = Attendance::where('school_year_id', $sy->id)
                ->whereDate('attendance_date', $date)
                ->whereIn('status', self::ATTENDED)->count();

            $rows[] = [
                'label' => $date->format('M j'),
                'present' => $present,
                'rate' => $enrolled > 0 ? (int) round($present / $enrolled * 100) : 0,
            ];
        }

        return $rows;
    }
}
