<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Services\AuditLogger;
use App\Services\Sf1ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Sf1Controller extends Controller
{
    public function __construct(
        private readonly Sf1ReportService $report,
        private readonly AuditLogger $audit,
    ) {}

    /** Picker: choose an advisory section. */
    public function index(Request $request)
    {
        // Like SF2, the School Register belongs to the class adviser.
        $sections = $request->user()->advisorySections()
            ->with(['gradeLevel', 'schoolYear'])
            ->orderByDesc('school_year_id')->orderBy('grade_level_id')->orderBy('name')
            ->get();

        return view('reports.sf1.index', ['sections' => $sections]);
    }

    /** Render the SF1 as a PDF streamed inline, matching the official layout. */
    public function show(Request $request, Section $section)
    {
        $this->authorizeSection($request, $section);

        $validated = $request->validate([
            'head' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
        ]);

        $data = $this->report->build($section);
        $this->audit->log('report_generated', $section, 'SF1 School Register generated');

        return Pdf::loadView('reports.sf1.print', $data + [
            'schoolHead' => trim((string) ($validated['head'] ?? '')),
            'district' => trim((string) ($validated['district'] ?? '')),
        ])
            ->setPaper('legal', 'landscape')
            ->stream($this->filename($section));
    }

    private function filename(Section $section): string
    {
        return Str::slug("SF1 School Register {$section->gradeLevel->name} {$section->name}").'.pdf';
    }

    private function authorizeSection(Request $request, Section $section): void
    {
        $user = $request->user();
        abort_unless(
            $user->isAdmin() || $user->advisorySections()->whereKey($section->id)->exists(),
            403,
            'SF1 covers advisory classes only — you are not the class adviser for this section.'
        );
    }
}
