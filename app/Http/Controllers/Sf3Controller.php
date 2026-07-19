<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Services\AuditLogger;
use App\Services\Sf3ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Sf3Controller extends Controller
{
    public function __construct(
        private readonly Sf3ReportService $report,
        private readonly AuditLogger $audit,
    ) {}

    /** Picker: choose an advisory section, mirroring SF1. */
    public function index(Request $request)
    {
        $sections = $request->user()->advisorySections()
            ->with(['gradeLevel', 'schoolYear'])
            ->orderByDesc('school_year_id')->orderBy('grade_level_id')->orderBy('name')
            ->get();

        return view('reports.sf3.index', ['sections' => $sections]);
    }

    /** Render the SF3 as a PDF streamed inline, matching the official layout. */
    public function show(Request $request, Section $section)
    {
        $this->authorizeSection($request, $section);

        $validated = $request->validate([
            'head' => ['nullable', 'string', 'max:120'],
        ]);

        $data = $this->report->build($section);
        $this->audit->log('report_generated', $section, 'SF3 Books Issued and Returned generated');

        return Pdf::loadView('reports.sf3.print', $data + [
            'schoolHead' => trim((string) ($validated['head'] ?? '')),
        ])
            ->setPaper('legal', 'landscape')
            ->stream(Str::slug("SF3 Books {$section->gradeLevel->name} {$section->name}").'.pdf');
    }

    private function authorizeSection(Request $request, Section $section): void
    {
        $user = $request->user();
        abort_unless(
            $user->isAdmin() || $user->advisorySections()->whereKey($section->id)->exists(),
            403,
            'SF3 covers advisory classes only — you are not the class adviser for this section.'
        );
    }
}
