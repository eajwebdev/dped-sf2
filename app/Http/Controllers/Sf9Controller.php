<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Services\AuditLogger;
use App\Services\Sf9ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * SF9 — Learner's Progress Report Card. Rendered as a locked inline PDF from
 * the grades/values the adviser enters plus the computed attendance record, so
 * the printed card reflects only what was input and cannot be altered.
 */
class Sf9Controller extends Controller
{
    public function __construct(
        private readonly Sf9ReportService $report,
        private readonly AuditLogger $audit,
    ) {}

    /** Picker: choose one of the adviser's advisory classes. */
    public function index(Request $request)
    {
        $sections = $request->user()->advisorySections()
            ->with(['gradeLevel', 'schoolYear', 'activeEnrollments.student'])
            ->withCount([
                'subjectAssignments as learning_areas_count',
                'activeEnrollments as learners_count',
            ])
            ->orderByDesc('school_year_id')->orderBy('grade_level_id')->orderBy('name')
            ->get();

        return view('reports.sf9.index', [
            'sections' => $sections,
            'school' => $request->user()->school,
        ]);
    }

    /** Stream the SF9 (one card per learner) as an inline PDF. */
    public function show(Request $request, Section $section)
    {
        $this->authorizeAdviser($request, $section);

        $validated = $request->validate([
            'head' => ['nullable', 'string', 'max:120'],
            'student' => ['nullable', 'integer'],
        ]);
        $schoolHead = trim((string) ($validated['head'] ?? ''));

        $data = $this->report->build($section);
        $one = $this->only($data, $validated['student'] ?? null);
        $this->audit->log('report_generated', $section, 'SF9 generated for '.($one ?? $section->name));

        return Pdf::loadView('reports.sf9.print', ['schoolHead' => $schoolHead] + $data)
            ->setPaper('a4', 'landscape')
            ->stream($this->filename($section, $one));
    }

    /**
     * Narrow the built learners to a single enrolment when a `student` was
     * chosen, and return that learner's name for the filename/audit — or null
     * for the whole class. 404s when the id is not in this section.
     */
    private function only(array &$data, ?int $enrollmentId): ?string
    {
        if (! $enrollmentId) {
            return null;
        }

        $learners = array_values(array_filter(
            $data['learners'],
            fn ($l) => (int) $l['enrollment_id'] === $enrollmentId,
        ));
        abort_if($learners === [], 404, 'That learner is not in this class.');

        $data['learners'] = $learners;

        return $learners[0]['name'] ?? null;
    }

    private function filename(Section $section, ?string $learner = null): string
    {
        $who = $learner ? " {$learner}" : '';

        return Str::slug("SF9 {$section->gradeLevel->name} {$section->name}{$who}").'.pdf';
    }

    private function authorizeAdviser(Request $request, Section $section): void
    {
        $user = $request->user();
        abort_unless(
            $user->isAdmin() || $user->advisorySections()->whereKey($section->id)->exists(),
            403,
            'SF9 is the class adviser\'s form — you are not the adviser of this section.'
        );
    }
}
