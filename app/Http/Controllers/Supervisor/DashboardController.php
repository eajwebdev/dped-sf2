<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Read-only overview for a school head: every class in their school for the
 * active year, grouped by adviser, with today's attendance status. Section
 * visibility comes from overseeableSections(), already tenant-scoped to the
 * supervisor's own school.
 */
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $activeYear = SchoolYear::activeFor($user);

        $sections = $user->overseeableSections()
            ->with(['gradeLevel', 'schoolYear', 'adviser'])
            ->withCount(['activeEnrollments as learners_count'])
            ->when($activeYear, fn ($q) => $q->where('school_year_id', $activeYear->id))
            ->orderBy('grade_level_id')->orderBy('name')
            ->get();

        $today = Carbon::today();

        // Which of these classes have already been marked today.
        $markedSectionIds = Attendance::whereIn('section_id', $sections->pluck('id'))
            ->whereDate('attendance_date', $today)
            ->distinct()
            ->pluck('section_id')
            ->all();

        // Group by adviser so the page reads as one card per teacher. Sections
        // without an adviser fall under a shared "Unassigned" bucket (id 0).
        $byAdviser = $sections->groupBy(fn ($section) => $section->adviser?->id ?? 0);

        return view('supervisor.dashboard', [
            'byAdviser' => $byAdviser,
            'sections' => $sections,
            'markedSectionIds' => $markedSectionIds,
            'activeYear' => $activeYear,
            'today' => $today,
        ]);
    }
}
