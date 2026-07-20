<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Services\InsightsService;
use Illuminate\Http\Request;

/**
 * Read-only Advanced Reports oversight for a school head: the same per-class
 * insight dashboard advisers see, for any class in their school. The dashboard
 * only reads records; the shared insights view swaps its edit actions for
 * read-only equivalents when a supervisor is viewing.
 */
class InsightsController extends Controller
{
    public function __construct(private readonly InsightsService $insights) {}

    /** Picker: every class in the supervisor's school, newest year first. */
    public function index(Request $request)
    {
        $sections = $request->user()->overseeableSections()
            ->with(['gradeLevel', 'schoolYear', 'adviser'])
            ->orderByDesc('school_year_id')
            ->orderBy('grade_level_id')->orderBy('name')
            ->get();

        return view('supervisor.insights.index', ['sections' => $sections]);
    }

    public function show(Request $request, Section $section)
    {
        abort_unless(
            $request->user()->overseeableSections()->whereKey($section->id)->exists(),
            403,
            'That class is not part of your school.'
        );

        // build() reads only the section; the user argument is unused there, so
        // passing the supervisor is safe and changes nothing in the output.
        return view('insights.show', $this->insights->build($request->user(), $section));
    }
}
