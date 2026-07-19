<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Services\AuditLogger;
use App\Services\Sf8ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Sf8Controller extends Controller
{
    public function __construct(
        private readonly Sf8ReportService $report,
        private readonly AuditLogger $audit,
    ) {}

    /** Picker: choose an advisory section. */
    public function index(Request $request)
    {
        // Like SF1, the Health and Nutrition Report belongs to the class adviser.
        $sections = $request->user()->advisorySections()
            ->with(['gradeLevel', 'schoolYear'])
            ->orderByDesc('school_year_id')->orderBy('grade_level_id')->orderBy('name')
            ->get();

        return view('reports.sf8.index', ['sections' => $sections]);
    }

    /** Render the SF8 as a PDF streamed inline, matching the official layout. */
    public function show(Request $request, Section $section)
    {
        $this->authorizeSection($request, $section);

        $validated = $request->validate([
            'district' => ['nullable', 'string', 'max:120'],
            'track' => ['nullable', 'string', 'max:120'],
            'date' => ['nullable', 'date'],
            'assessed' => ['nullable', 'string', 'max:120'],
            'certified' => ['nullable', 'string', 'max:120'],
            'reviewed' => ['nullable', 'string', 'max:120'],
        ]);

        $assessmentDate = filled($validated['date'] ?? null)
            ? Carbon::parse($validated['date'])
            : null;

        $data = $this->report->build($section, $assessmentDate);
        $this->audit->log('report_generated', $section, 'SF8 Health and Nutrition Report generated');

        return Pdf::loadView('reports.sf8.print', $data + [
            'district' => trim((string) ($validated['district'] ?? '')),
            'track' => trim((string) ($validated['track'] ?? '')),
            'assessmentDate' => $assessmentDate,
            'assessedBy' => trim((string) ($validated['assessed'] ?? '')),
            'certifiedBy' => trim((string) ($validated['certified'] ?? '')),
            'reviewedBy' => trim((string) ($validated['reviewed'] ?? '')),
        ])
            ->setPaper('a4', 'portrait')
            ->stream($this->filename($section));
    }

    private function filename(Section $section): string
    {
        return Str::slug("SF8 Health and Nutrition {$section->gradeLevel->name} {$section->name}").'.pdf';
    }

    private function authorizeSection(Request $request, Section $section): void
    {
        $user = $request->user();
        abort_unless(
            $user->isAdmin() || $user->advisorySections()->whereKey($section->id)->exists(),
            403,
            'SF8 covers advisory classes only — you are not the class adviser for this section.'
        );
    }
}
