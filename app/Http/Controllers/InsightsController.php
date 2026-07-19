<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Services\InsightsService;
use Illuminate\Http\Request;

/**
 * Advanced Reports (Professional plan): per-class insight dashboards for the
 * adviser's own sections.
 */
class InsightsController extends Controller
{
    public function __construct(private readonly InsightsService $insights) {}

    /** Picker: the adviser's classes, newest school year first. */
    public function index(Request $request)
    {
        $sections = $request->user()->advisorySections()
            ->with(['gradeLevel', 'schoolYear'])
            ->orderByDesc('school_year_id')->orderBy('grade_level_id')->orderBy('name')
            ->get();

        return view('insights.index', ['sections' => $sections]);
    }

    public function show(Request $request, Section $section)
    {
        $user = $request->user();
        abort_unless(
            $user->isAdmin() || $user->advisorySections()->whereKey($section->id)->exists(),
            403,
            'Insights cover your own advisory classes only.'
        );

        return view('insights.show', $this->insights->build($user, $section));
    }
}
