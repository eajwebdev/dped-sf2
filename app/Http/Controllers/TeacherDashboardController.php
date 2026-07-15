<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TeacherDashboardController extends Controller
{
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
        ]);
    }
}
