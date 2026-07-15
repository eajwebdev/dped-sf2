<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Models\TeacherSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ScanPortalController extends Controller
{
    /**
     * Full-screen QR attendance portal. Schedule-aware: the class happening
     * right now (from the teacher's timetable) is pre-selected, so the device
     * can be handed to students to scan themselves present.
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now();
        $activeYear = SchoolYear::active()->first();

        // All sections this user can take attendance for (advisory + subject teaching).
        $sections = $user->accessibleSections()
            ->with(['gradeLevel'])
            ->when($activeYear, fn ($q) => $q->where('school_year_id', $activeYear->id))
            ->orderBy('grade_level_id')->orderBy('name')
            ->get();

        $currentClass = null;
        $todayClasses = collect();

        if ($teacher = $user->teacher) {
            $todayClasses = TeacherSchedule::with(['section.gradeLevel', 'subject'])
                ->where('teacher_id', $teacher->id)
                ->when($activeYear, fn ($q) => $q->where('school_year_id', $activeYear->id))
                ->where('day_of_week', $now->isoWeekday())
                ->orderBy('start_time')
                ->get();

            $currentClass = $todayClasses->first(fn ($s) => $s->start_time <= $now->format('H:i:s')
                && $s->end_time > $now->format('H:i:s'));
        }

        return view('teacher.portal', [
            'sections' => $sections,
            'todayClasses' => $todayClasses,
            'currentClass' => $currentClass,
            'now' => $now,
        ]);
    }
}
