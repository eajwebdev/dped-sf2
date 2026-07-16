<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\GradeLevel;
use App\Services\CuttingClassService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TeacherDashboardController extends Controller
{
    public function __construct(private readonly CuttingClassService $cutting) {}

    public function __invoke(Request $request)
    {
        $user = $request->user();

        $sections = $user->accessibleSections()
            ->with(['gradeLevel', 'schoolYear'])
            ->withCount(['activeEnrollments as learners_count'])
            ->whereHas('schoolYear', fn ($q) => $q->where('is_active', true))
            ->orderBy('grade_level_id')->orderBy('name')
            ->get();

        $today = Carbon::today();
        $markedToday = Attendance::whereIn('section_id', $sections->pluck('id'))
            ->whereDate('attendance_date', $today)
            ->distinct('section_id')
            ->count('section_id');

        return view('teacher.dashboard', [
            'sections' => $sections,
            'today' => $today,
            'markedToday' => $markedToday,
            'gradeLevels' => GradeLevel::orderBy('id')->get(),
            'cuttingToday' => $this->cutting->countForAdviser($user, $today),
            'promotable' => $this->promotableCount($user),
        ]);
    }

    /**
     * Advisory learners from previous years still without an enrollment in the
     * active year — the ones waiting to be moved up by their adviser.
     */
    private function promotableCount($user): int
    {
        $teacherId = $user->teacher?->id;
        $activeYear = \App\Models\SchoolYear::activeFor($user);

        if (! $teacherId || ! $activeYear) {
            return 0;
        }

        return \App\Models\StudentEnrollment::query()
            ->whereIn('status', [
                \App\Models\StudentEnrollment::STATUS_ENROLLED,
                \App\Models\StudentEnrollment::STATUS_TRANSFERRED_IN,
            ])
            ->whereHas('section', fn ($q) => $q->where('adviser_id', $teacherId)
                ->where('school_year_id', '!=', $activeYear->id))
            ->whereNotExists(fn ($q) => $q->from('student_enrollments as cur')
                ->whereColumn('cur.student_id', 'student_enrollments.student_id')
                ->where('cur.school_year_id', $activeYear->id))
            ->count();
    }
}
