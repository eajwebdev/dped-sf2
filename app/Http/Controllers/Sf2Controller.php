<?php

namespace App\Http\Controllers;

use App\Exports\Sf2Export;
use App\Models\Section;
use App\Services\AuditLogger;
use App\Services\Sf2ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class Sf2Controller extends Controller
{
    public function __construct(
        private readonly Sf2ReportService $report,
        private readonly AuditLogger $audit,
    ) {}

    /** Picker: choose an accessible section + month. */
    public function index(Request $request)
    {
        $sections = $request->user()->accessibleSections()
            ->with(['gradeLevel', 'schoolYear'])
            ->orderByDesc('school_year_id')->orderBy('grade_level_id')->orderBy('name')
            ->get();

        return view('reports.sf2.index', [
            'sections' => $sections,
            'year' => (int) $request->get('year', now()->year),
            'month' => (int) $request->get('month', now()->month),
        ]);
    }

    public function show(Request $request, Section $section)
    {
        [$year, $month] = $this->resolvePeriod($request, $section);
        $this->authorizeSection($request, $section);

        $data = $this->report->build($section, $year, $month);

        return view('reports.sf2.print', ['data' => $data] + $data);
    }

    public function pdf(Request $request, Section $section)
    {
        [$year, $month] = $this->resolvePeriod($request, $section);
        $this->authorizeSection($request, $section);

        $data = $this->report->build($section, $year, $month);
        $this->audit->log('report_generated', $section, "SF2 PDF generated for {$data['monthLabel']}");

        $pdf = Pdf::loadView('reports.sf2.print', ['data' => $data, 'pdf' => true] + $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download($this->filename($section, $data, 'pdf'));
    }

    public function excel(Request $request, Section $section)
    {
        [$year, $month] = $this->resolvePeriod($request, $section);
        $this->authorizeSection($request, $section);

        $data = $this->report->build($section, $year, $month);
        $this->audit->log('report_generated', $section, "SF2 Excel generated for {$data['monthLabel']}");

        return Excel::download(new Sf2Export($data), $this->filename($section, $data, 'xlsx'));
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
            $user->isAdmin() || $user->accessibleSections()->whereKey($section->id)->exists(),
            403,
            'You do not have access to this section.'
        );
    }
}
