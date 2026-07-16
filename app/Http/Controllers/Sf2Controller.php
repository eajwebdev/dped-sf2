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

    /** Picker: choose an advisory section + month. */
    public function index(Request $request)
    {
        // SF2 is the class adviser's form: sections merely taught are excluded.
        $sections = $request->user()->advisorySections()
            ->with(['gradeLevel', 'schoolYear'])
            ->orderByDesc('school_year_id')->orderBy('grade_level_id')->orderBy('name')
            ->get();

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

        $data = $this->report->build($section, $year, $month);
        $this->audit->log('report_generated', $section, "SF2 generated for {$data['monthLabel']}");

        return Pdf::loadView('reports.sf2.print', ['data' => $data, 'pdf' => true] + $data)
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
        abort_unless(
            $user->isAdmin() || $user->advisorySections()->whereKey($section->id)->exists(),
            403,
            'SF2 covers advisory classes only — you are not the class adviser for this section.'
        );
    }
}
