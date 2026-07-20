<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\LearnerGrade;
use App\Models\LearnerValue;
use App\Models\Section;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Services\AuditLogger;
use App\Services\Sf9ReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Adviser's entry screen for the SF9 report card: per-subject quarterly grades
 * and core-values marks for every learner in the advisory class. What is saved
 * here is what the locked SF9 PDF renders.
 */
class Sf9GradeController extends Controller
{
    /** Default DepEd learning areas offered as a one-click starter for JHS. */
    private const STANDARD_JHS_AREAS = [
        'Filipino', 'English', 'Mathematics', 'Science', 'Araling Panlipunan',
        'Edukasyon sa Pagpapakatao', 'Edukasyong Pantahanan at Pangkabuhayan', 'MAPEH',
    ];

    public function __construct(
        private readonly Sf9ReportService $report,
        private readonly AuditLogger $audit,
    ) {}

    public function edit(Request $request, Section $section)
    {
        $this->authorizeAdviser($request, $section);
        $section->load(['gradeLevel', 'schoolYear']);

        $roster = $this->report->roster($section);
        // Assignments (with subject) drive both the removable chip list and the
        // grade columns, so the delete action has an id to target.
        $assignments = SubjectAssignment::with('subject')
            ->where('section_id', $section->id)
            ->get()
            ->filter(fn ($a) => $a->subject)
            ->sortBy(fn ($a) => $a->subject->name)
            ->values();
        $enrollmentIds = $roster->pluck('id');

        // existing[enrollment][subject][period] = grade
        $existingGrades = LearnerGrade::whereIn('student_enrollment_id', $enrollmentIds)->get()
            ->groupBy('student_enrollment_id')
            ->map(fn ($g) => $g->groupBy('subject_id')->map(fn ($s) => $s->keyBy('period')->map->grade));

        // existingValues[enrollment][core_value][behavior][period] = mark
        $existingValues = LearnerValue::whereIn('student_enrollment_id', $enrollmentIds)->get()
            ->groupBy('student_enrollment_id')
            ->map(fn ($v) => $v->groupBy('core_value')
                ->map(fn ($c) => $c->groupBy('behavior')->map(fn ($b) => $b->keyBy('period')->map->mark)));

        return view('teacher.sf9.grades', [
            'section' => $section,
            'roster' => $roster,
            'assignments' => $assignments,
            'periodLabels' => $this->report->periodLabels($section),
            'isShs' => $this->report->isSeniorHigh($section),
            'coreValues' => LearnerValue::CORE_VALUES,
            'behaviors' => LearnerValue::BEHAVIORS,
            'marks' => LearnerValue::MARKS,
            'passingGrade' => Sf9ReportService::PASSING_GRADE,
            'existingGrades' => $existingGrades,
            'existingValues' => $existingValues,
        ]);
    }

    public function save(Request $request, Section $section): RedirectResponse
    {
        $this->authorizeAdviser($request, $section);

        $request->validate([
            'grades' => ['array'],
            'grades.*.*.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'values' => ['array'],
            'values.*.*.*.*' => ['nullable', Rule::in(array_keys(LearnerValue::MARKS))],
        ]);

        $enrollmentIds = $this->report->roster($section)->pluck('id')->all();
        $subjectIds = $this->report->subjects($section)->pluck('id')->all();

        DB::transaction(function () use ($request, $section, $enrollmentIds, $subjectIds) {
            foreach ((array) $request->input('grades', []) as $enrollmentId => $bySubject) {
                if (! in_array((int) $enrollmentId, $enrollmentIds, true)) {
                    continue;
                }
                foreach ((array) $bySubject as $subjectId => $byPeriod) {
                    if (! in_array((int) $subjectId, $subjectIds, true)) {
                        continue;
                    }
                    foreach ((array) $byPeriod as $period => $grade) {
                        $this->upsertGrade($section, (int) $enrollmentId, (int) $subjectId, (int) $period, $grade);
                    }
                }
            }

            foreach ((array) $request->input('values', []) as $enrollmentId => $byCore) {
                if (! in_array((int) $enrollmentId, $enrollmentIds, true)) {
                    continue;
                }
                foreach ((array) $byCore as $coreValue => $byBehavior) {
                    if (! array_key_exists($coreValue, LearnerValue::CORE_VALUES)) {
                        continue;
                    }
                    $behaviorCount = LearnerValue::behaviorCount($coreValue);
                    foreach ((array) $byBehavior as $behavior => $byPeriod) {
                        if ((int) $behavior < 1 || (int) $behavior > $behaviorCount) {
                            continue;
                        }
                        foreach ((array) $byPeriod as $period => $mark) {
                            $this->upsertValue($section, (int) $enrollmentId, $coreValue, (int) $behavior, (int) $period, $mark);
                        }
                    }
                }
            }
        });

        $this->audit->log('sf9_grades_saved', $section, "SF9 grades saved for {$section->name}");

        return back()->with('success', 'SF9 grades and values saved.');
    }

    public function addSubject(Request $request, Section $section): RedirectResponse
    {
        $this->authorizeAdviser($request, $section);
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);

        $this->attachSubject($section, trim($data['name']));

        return back()->with('success', 'Learning area added.');
    }

    public function addStandardAreas(Request $request, Section $section): RedirectResponse
    {
        $this->authorizeAdviser($request, $section);

        foreach (self::STANDARD_JHS_AREAS as $name) {
            $this->attachSubject($section, $name);
        }

        return back()->with('success', 'Standard learning areas added.');
    }

    public function removeSubject(Request $request, SubjectAssignment $subjectAssignment): RedirectResponse
    {
        $this->authorizeAdviser($request, $subjectAssignment->section);
        $subjectAssignment->delete(); // learner grades for it are kept but no longer shown

        return back()->with('success', 'Learning area removed.');
    }

    private function attachSubject(Section $section, string $name): void
    {
        if ($name === '') {
            return;
        }

        $subject = Subject::firstOrCreate(
            ['name' => $name],
            ['code' => $this->uniqueCode($name), 'is_active' => true],
        );

        SubjectAssignment::firstOrCreate(
            ['section_id' => $section->id, 'subject_id' => $subject->id],
            ['school_year_id' => $section->school_year_id, 'grade_level_id' => $section->grade_level_id],
        );
    }

    /** A school-unique subject code derived from the name. */
    private function uniqueCode(string $name): string
    {
        $base = strtoupper(Str::of($name)->slug('')->substr(0, 6)) ?: 'SUBJ';

        do {
            $code = $base.random_int(100, 999);
        } while (Subject::where('code', $code)->exists());

        return $code;
    }

    private function upsertGrade(Section $section, int $enrollmentId, int $subjectId, int $period, $grade): void
    {
        if (! in_array($period, LearnerGrade::PERIODS, true)) {
            return;
        }

        $key = ['student_enrollment_id' => $enrollmentId, 'subject_id' => $subjectId, 'period' => $period];

        if ($grade === null || $grade === '') {
            LearnerGrade::where($key)->delete();

            return;
        }

        LearnerGrade::updateOrCreate($key, ['school_id' => $section->school_id, 'grade' => $grade]);
    }

    private function upsertValue(Section $section, int $enrollmentId, string $coreValue, int $behavior, int $period, $mark): void
    {
        if (! in_array($period, LearnerGrade::PERIODS, true)) {
            return;
        }

        $key = ['student_enrollment_id' => $enrollmentId, 'core_value' => $coreValue, 'behavior' => $behavior, 'period' => $period];

        if ($mark === null || $mark === '') {
            LearnerValue::where($key)->delete();

            return;
        }

        LearnerValue::updateOrCreate($key, ['school_id' => $section->school_id, 'mark' => $mark]);
    }

    private function authorizeAdviser(Request $request, Section $section): void
    {
        $user = $request->user();
        abort_unless(
            $user->isAdmin() || $user->advisorySections()->whereKey($section->id)->exists(),
            403,
            "SF9 is the class adviser's form — you are not the adviser of this section."
        );
    }
}
