<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Services\AuditLogger;
use App\Services\Sf2ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Sf2Controller extends Controller
{
    public function __construct(
        private readonly Sf2ReportService $report,
        private readonly AuditLogger $audit,
    ) {}

    /**
     * Picker: choose a class + month. The form offers both the teacher's
     * advisory classes and the classes they merely teach a subject in; each
     * section carries an `is_advisory` flag so the form can switch between them.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Merge advisory + non-advisory, de-duped by id (an admin sees every
        // section through both scopes — keep the first, advisory-tagged copy).
        $sections = collect();
        foreach ($user->advisorySections()->with(['gradeLevel', 'schoolYear'])->get() as $section) {
            $section->setAttribute('is_advisory', true);
            $sections[$section->id] = $section;
        }
        foreach ($user->nonAdvisorySections()->with(['gradeLevel', 'schoolYear'])->get() as $section) {
            if (! $sections->has($section->id)) {
                $section->setAttribute('is_advisory', false);
                $sections[$section->id] = $section;
            }
        }

        $sections = $sections->values()
            ->sortByDesc('school_year_id')
            ->values();

        return view('reports.sf2.index', [
            'sections' => $sections,
            'year' => (int) $request->get('year', now()->year),
            'month' => (int) $request->get('month', now()->month),
        ]);
    }

    /**
     * Render the SF2 as a PDF streamed inline, so the browser tab displays it
     * with its own print/save controls instead of forcing a download.
     */
    public function show(Request $request, Section $section)
    {
        [$year, $month] = $this->resolvePeriod($request, $section);
        $this->authorizeSection($request, $section);

        $validated = $request->validate(['head' => ['nullable', 'string', 'max:120']]);
        $schoolHead = trim((string) ($validated['head'] ?? ''));

        $data = $this->report->build($section, $year, $month);
        $this->audit->log('report_generated', $section, "SF2 generated for {$data['monthLabel']}");

        return Pdf::loadView('reports.sf2.print', ['data' => $data, 'pdf' => true, 'schoolHead' => $schoolHead] + $data)
            ->setPaper('a4', 'landscape')
            ->stream($this->filename($section, $data, 'pdf'));
    }

    /** @return array{0:int,1:int} [year, month] */
    private function resolvePeriod(Request $request, Section $section): array
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        return [
            (int) ($validated['year'] ?? $section->schoolYear->start_date->year),
            (int) ($validated['month'] ?? now()->month),
        ];
    }

    private function filename(Section $section, array $data, string $ext): string
    {
        $slug = Str::slug("SF2 {$section->gradeLevel->name} {$section->name} {$data['monthLabel']}");

        return "{$slug}.{$ext}";
    }

    private function authorizeSection(Request $request, Section $section): void
    {
        $user = $request->user();
        // Advisers print the SF2 for classes they advise; subject teachers for
        // classes they teach — both cover the same per-section daily attendance.
        abort_unless(
            $user->isAdmin() || $user->accessibleSections()->whereKey($section->id)->exists(),
            403,
            'You do not advise or teach this section.'
        );
    }
}
