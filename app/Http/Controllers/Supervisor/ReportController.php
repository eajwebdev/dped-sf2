<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Services\AuditLogger;
use App\Services\Sf1ReportService;
use App\Services\Sf3ReportService;
use App\Services\Sf5ReportService;
use App\Services\Sf8ReportService;
use App\Services\Sf9ReportService;
use App\Services\Sf10ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Read-only oversight of the adviser School Forms (SF1, SF5, SF8, SF9) for a
 * school head. Each action reuses the same report service and print view the
 * adviser uses, so the PDF is byte-for-byte the adviser's — this controller
 * only reads. Section access flows through overseeableSections(), tenant-scoped
 * to the supervisor's own school; there is no save endpoint here.
 */
class ReportController extends Controller
{
    public function __construct(
        private readonly Sf1ReportService $sf1,
        private readonly Sf3ReportService $sf3,
        private readonly Sf5ReportService $sf5,
        private readonly Sf8ReportService $sf8,
        private readonly Sf9ReportService $sf9,
        private readonly Sf10ReportService $sf10,
        private readonly AuditLogger $audit,
    ) {}

    // ── SF1 — School Register ────────────────────────────────────────────────
    public function sf1Index(Request $request)
    {
        return $this->picker($request, 'sf1', 'SF1 — School Register',
            'The class master list for any class in your school.');
    }

    public function sf1Show(Request $request, Section $section)
    {
        $this->authorizeSection($request, $section);
        $v = $this->headerFields($request);

        $data = $this->sf1->build($section);
        $this->log($section, 'SF1 School Register');

        return Pdf::loadView('reports.sf1.print', $data + [
            'schoolHead' => $v['head'],
            'district' => $v['district'],
        ])->setPaper('legal', 'landscape')
            ->stream($this->filename('SF1 School Register', $section));
    }

    // ── SF3 — Books Issued & Returned ────────────────────────────────────────
    public function sf3Index(Request $request)
    {
        return $this->picker($request, 'sf3', 'SF3 — Books Issued & Returned',
            'The textbook issuance record for any class in your school.');
    }

    public function sf3Show(Request $request, Section $section)
    {
        $this->authorizeSection($request, $section);
        $v = $this->headerFields($request);

        $data = $this->sf3->build($section);
        $this->log($section, 'SF3 Books Issued and Returned');

        return Pdf::loadView('reports.sf3.print', $data + [
            'schoolHead' => $v['head'],
        ])->setPaper('legal', 'landscape')
            ->stream($this->filename('SF3 Books', $section));
    }

    // ── SF5 — Promotion & Proficiency ────────────────────────────────────────
    public function sf5Index(Request $request)
    {
        return $this->picker($request, 'sf5', 'SF5 — Promotion & Proficiency',
            'End-of-year promotion and proficiency for any class in your school.');
    }

    public function sf5Show(Request $request, Section $section)
    {
        $this->authorizeSection($request, $section);
        $v = $this->headerFields($request, ['reviewer' => ['nullable', 'string', 'max:120'], 'curriculum' => ['nullable', 'string', 'max:60']]);

        $data = $this->sf5->build($section);
        $this->log($section, 'SF5 Promotion & Proficiency');

        return Pdf::loadView('reports.sf5.print', $data + [
            'schoolHead' => $v['head'],
            'reviewer' => $v['reviewer'] ?? '',
            'district' => $v['district'],
            'curriculum' => ($v['curriculum'] ?? '') ?: 'K to 12',
        ])->setPaper('legal', 'landscape')
            ->stream($this->filename('SF5 Promotion', $section));
    }

    // ── SF8 — Health & Nutrition ─────────────────────────────────────────────
    public function sf8Index(Request $request)
    {
        return $this->picker($request, 'sf8', 'SF8 — Health & Nutrition',
            'The Basic Health and Nutrition Report for any class in your school.');
    }

    public function sf8Show(Request $request, Section $section)
    {
        $this->authorizeSection($request, $section);
        $v = $this->headerFields($request, ['track' => ['nullable', 'string', 'max:120']]);

        $data = $this->sf8->build($section, null);
        $this->log($section, 'SF8 Health and Nutrition Report');

        return Pdf::loadView('reports.sf8.print', $data + [
            'district' => $v['district'],
            'track' => $v['track'] ?? '',
            'assessmentDate' => null,
            'assessedBy' => '',
            'certifiedBy' => '',
            'reviewedBy' => '',
        ])->setPaper('a4', 'portrait')
            ->stream($this->filename('SF8 Health and Nutrition', $section));
    }

    // ── SF9 — Learner Progress Report Card ───────────────────────────────────
    public function sf9Index(Request $request)
    {
        return $this->picker($request, 'sf9', 'SF9 — Report Card',
            'The learner progress report cards for any class in your school.');
    }

    public function sf9Show(Request $request, Section $section)
    {
        $this->authorizeSection($request, $section);
        $v = $this->headerFields($request);

        $data = $this->sf9->build($section);
        $one = $this->only($data, $request->integer('student') ?: null);
        $this->log($section, 'SF9 Report Card');

        return Pdf::loadView('reports.sf9.print', ['schoolHead' => $v['head']] + $data)
            ->setPaper('a4', 'landscape')
            ->stream($this->filename('SF9'.($one ? " {$one}" : ''), $section));
    }

    // ── SF10 — Learner Permanent Academic Record ─────────────────────────────
    public function sf10Index(Request $request)
    {
        return $this->picker($request, 'sf10', 'SF10 — Permanent Record',
            'The learner permanent academic record for any class in your school.');
    }

    public function sf10Show(Request $request, Section $section)
    {
        $this->authorizeSection($request, $section);
        $v = $this->headerFields($request);

        $data = $this->sf10->build($section);
        $one = $this->only($data, $request->integer('student') ?: null);
        $this->log($section, 'SF10 Permanent Academic Record');

        // A class-sized roster of full SF10-ES pages exceeds DomPDF's default
        // 512M memory budget.
        ini_set('memory_limit', '1024M');

        return Pdf::loadView('reports.sf10.print', $data + [
            'schoolHead' => $v['head'],
            'district' => $v['district'],
        ])->setPaper('letter', 'portrait')
            ->stream($this->filename('SF10'.($one ? " {$one}" : ''), $section));
    }

    /**
     * Narrow the built learners to a single enrolment when a `student` was
     * chosen; returns that learner's name, or null for the whole class. 404s
     * when the id is not part of this section.
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

    // ── Shared helpers ───────────────────────────────────────────────────────

    /** Render the generic oversight picker for the given form. */
    private function picker(Request $request, string $form, string $title, string $subtitle)
    {
        return view('supervisor.reports.picker', [
            'sections' => $this->sections($request),
            'form' => $form,
            'title' => $title,
            'subtitle' => $subtitle,
        ]);
    }

    /** Every class in the supervisor's school, newest year first. */
    private function sections(Request $request): Collection
    {
        return $request->user()->overseeableSections()
            ->with(['gradeLevel', 'schoolYear', 'adviser'])
            ->orderByDesc('school_year_id')
            ->orderBy('grade_level_id')->orderBy('name')
            ->get();
    }

    /**
     * The optional print-header fields. `head` and `district` are common to all
     * forms; callers pass any form-specific extras to merge into the rules.
     *
     * @param  array<string, array<int, string>>  $extra
     * @return array<string, string>
     */
    private function headerFields(Request $request, array $extra = []): array
    {
        $validated = $request->validate([
            'head' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
        ] + $extra);

        return array_map(fn ($v) => trim((string) $v), array_merge(
            ['head' => '', 'district' => ''],
            array_filter($validated, fn ($v) => $v !== null),
        ));
    }

    private function filename(string $label, Section $section): string
    {
        return Str::slug("{$label} {$section->gradeLevel->name} {$section->name}").'.pdf';
    }

    private function log(Section $section, string $label): void
    {
        $this->audit->log('report_generated', $section, "{$label} viewed via oversight");
    }

    /**
     * Defence in depth: route-model binding already applies Section's tenant
     * scope, so another school's id 404s before reaching here.
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
