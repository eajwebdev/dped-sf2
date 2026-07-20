<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Services\AuditLogger;
use App\Services\Sf2ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * SF2 oversight: a school head may view and print any class's SF2 in their own
 * school, but never edit it. Read-only by construction — no save endpoints
 * exist here, and the report is rendered from data the adviser entered.
 */
class Sf2Controller extends Controller
{
    public function __construct(
        private readonly Sf2ReportService $report,
        private readonly AuditLogger $audit,
    ) {}

    /** Picker: every class in the school, newest year first. */
    public function index(Request $request)
    {
        $sections = $request->user()->overseeableSections()
            ->with(['gradeLevel', 'schoolYear', 'adviser'])
            ->orderByDesc('school_year_id')
            ->orderBy('grade_level_id')->orderBy('name')
            ->get();

        return view('supervisor.sf2.index', [
            'sections' => $sections,
            'year' => (int) $request->get('year', now()->year),
            'month' => (int) $request->get('month', now()->month),
        ]);
    }

    /** Stream the SF2 as an inline PDF, identical to the adviser's output. */
    public function show(Request $request, Section $section)
    {
        $this->authorizeSection($request, $section);

        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'head' => ['nullable', 'string', 'max:120'],
        ]);

        $year = (int) ($validated['year'] ?? $section->schoolYear->start_date->year);
        $month = (int) ($validated['month'] ?? now()->month);
        $schoolHead = trim((string) ($validated['head'] ?? ''));

        $data = $this->report->build($section, $year, $month);
        $this->audit->log('report_generated', $section, "SF2 viewed via oversight for {$data['monthLabel']}");

        return Pdf::loadView('reports.sf2.print', ['data' => $data, 'pdf' => true, 'schoolHead' => $schoolHead] + $data)
            ->setPaper('a4', 'landscape')
            ->stream($this->filename($section, $data));
    }

    private function filename(Section $section, array $data): string
    {
        return Str::slug("SF2 {$section->gradeLevel->name} {$section->name} {$data['monthLabel']}").'.pdf';
    }

    /**
     * Defence in depth: route-model binding already applies Section's tenant
     * scope, so another school's id 404s before reaching here. This second
     * check means the endpoint stays safe even if that binding ever changes.
     */
    private function authorizeSection(Request $request, Section $section): void
    {
        abort_unless(
            $request->user()->overseeableSections()->whereKey($section->id)->exists(),
            403,
            'That class is not part of your school.'
        );
    }
}
