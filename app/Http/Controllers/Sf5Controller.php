<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Services\AuditLogger;
use App\Services\Sf5ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Sf5Controller extends Controller
{
    public function __construct(
        private readonly Sf5ReportService $report,
        private readonly AuditLogger $audit,
    ) {}

    /** Picker: choose an advisory section, mirroring the other School Forms. */
    public function index(Request $request)
    {
        $sections = $request->user()->advisorySections()
            ->with(['gradeLevel', 'schoolYear'])
            ->orderByDesc('school_year_id')->orderBy('grade_level_id')->orderBy('name')
            ->get();

        return view('reports.sf5.index', ['sections' => $sections]);
    }

    /** Render the SF5 as a PDF streamed inline, matching the official layout. */
    public function show(Request $request, Section $section)
    {
        $this->authorizeSection($request, $section);

        $validated = $request->validate([
            'head' => ['nullable', 'string', 'max:120'],
            'reviewer' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'curriculum' => ['nullable', 'string', 'max:60'],
        ]);

        $data = $this->report->build($section);
        $this->audit->log('report_generated', $section, 'SF5 Promotion & Proficiency generated');

        return Pdf::loadView('reports.sf5.print', $data + [
            'schoolHead' => trim((string) ($validated['head'] ?? '')),
            'reviewer' => trim((string) ($validated['reviewer'] ?? '')),
            'district' => trim((string) ($validated['district'] ?? '')),
            'curriculum' => trim((string) ($validated['curriculum'] ?? '')) ?: 'K to 12',
        ])
            ->setPaper('legal', 'landscape')
            ->stream(Str::slug("SF5 Promotion {$section->gradeLevel->name} {$section->name}").'.pdf');
    }

    /** The adviser's entry screen: general averages and incomplete subjects. */
    public function grades(Request $request, Section $section)
    {
        $this->authorizeSection($request, $section);

        $section->load(['gradeLevel', 'schoolYear']);

        $roster = StudentEnrollment::with('student')
            ->where('section_id', $section->id)
            ->get()
            ->sortBy(fn ($e) => ($e->student->gender === 'Male' ? '0' : '1')
                .mb_strtolower($e->student->last_name.' '.$e->student->first_name))
            ->values();

        return view('teacher.sf5.grades', [
            'section' => $section,
            'roster' => $roster,
            'service' => $this->report,
        ]);
    }

    /** Bulk save: one row of inputs per learner, validated as a set. */
    public function saveGrades(Request $request, Section $section): RedirectResponse
    {
        $this->authorizeSection($request, $section);

        $data = $request->validate([
            'rows' => ['required', 'array'],
            'rows.*.enrollment_id' => ['required', 'integer'],
            'rows.*.general_average' => ['nullable', 'numeric', 'min:60', 'max:100'],
            'rows.*.is_irregular' => ['nullable', 'boolean'],
            'rows.*.subjects_completed' => ['nullable', 'string', 'max:255'],
            'rows.*.subjects_incomplete' => ['nullable', 'string', 'max:255'],
        ]);

        // Fetch the section's own enrollments once; anything else in the
        // payload is silently ignored rather than written cross-section.
        $enrollments = StudentEnrollment::where('section_id', $section->id)
            ->whereIn('id', collect($data['rows'])->pluck('enrollment_id'))
            ->get()
            ->keyBy('id');

        $saved = 0;
        foreach ($data['rows'] as $row) {
            $enrollment = $enrollments->get((int) $row['enrollment_id']);
            if (! $enrollment) {
                continue;
            }

            $enrollment->update([
                'general_average' => $row['general_average'] ?? null,
                'is_irregular' => (bool) ($row['is_irregular'] ?? false),
                'subjects_completed' => $row['subjects_completed'] ?? null,
                'subjects_incomplete' => $row['subjects_incomplete'] ?? null,
            ]);
            $saved++;
        }

        $this->audit->log('sf5_grades_saved', $section,
            "SF5 averages saved for {$saved} learner(s) in {$section->name}");

        return back()->with('success', "Saved {$saved} ".str('record')->plural($saved).'.');
    }

    private function authorizeSection(Request $request, Section $section): void
    {
        $user = $request->user();
        abort_unless(
            $user->isAdmin() || $user->advisorySections()->whereKey($section->id)->exists(),
            403,
            'SF5 covers advisory classes only — you are not the class adviser for this section.'
        );
    }
}
