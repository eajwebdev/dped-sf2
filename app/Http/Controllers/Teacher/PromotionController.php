<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Services\PromotionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Adviser-driven "move my class up": the previous-year adviser picks which
 * learners advance and they land in a next-grade section the same teacher
 * advises in the active year (created on the fly when missing) — the class
 * keeps its adviser across school years with no admin involvement.
 */
class PromotionController extends Controller
{
    public function __construct(private readonly PromotionService $promotion) {}

    public function index(Request $request)
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher !== null, 403, 'Your account is not linked to a teacher record.');

        $activeYear = SchoolYear::activeFor($request->user());

        $sources = $this->promotableSources($teacher->id, $activeYear);
        $source = $request->filled('section_id')
            ? $sources->firstWhere('id', $request->integer('section_id'))
            : $sources->first();

        $learners = collect();
        $nextGrade = null;
        $existingTarget = null;
        $targetOptions = collect();

        if ($source && $activeYear) {
            $nextGrade = $source->gradeLevel->nextGrade();

            // Learners still without an enrollment in the active year.
            $inActive = StudentEnrollment::where('school_year_id', $activeYear->id)->pluck('student_id')->flip();
            $learners = StudentEnrollment::with('student')
                ->where('section_id', $source->id)
                ->whereIn('status', [StudentEnrollment::STATUS_ENROLLED, StudentEnrollment::STATUS_TRANSFERRED_IN])
                ->get()
                ->sortBy(fn ($e) => [$e->student->gender, $e->student->last_name])
                ->values()
                ->map(fn ($e) => ['enrollment' => $e, 'alreadyMoved' => $inActive->has($e->student_id)]);

            if ($nextGrade) {
                $existingTarget = Section::where('school_year_id', $activeYear->id)
                    ->where('grade_level_id', $nextGrade->id)
                    ->where('adviser_id', $teacher->id)
                    ->first();

                // Learners may advance under a DIFFERENT adviser: every next-grade
                // class this year is a valid destination — its adviser becomes theirs.
                $targetOptions = Section::with('adviser')
                    ->where('school_year_id', $activeYear->id)
                    ->where('grade_level_id', $nextGrade->id)
                    ->when($existingTarget, fn ($q) => $q->whereKeyNot($existingTarget->id))
                    ->orderBy('name')
                    ->get();
            }
        }

        return view('teacher.promotion', [
            'sources' => $sources,
            'source' => $source,
            'learners' => $learners,
            'activeYear' => $activeYear,
            'nextGrade' => $nextGrade,
            'existingTarget' => $existingTarget,
            'targetOptions' => $targetOptions,
        ]);
    }

    public function promote(Request $request): RedirectResponse
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher !== null, 403, 'Your account is not linked to a teacher record.');

        $activeYear = SchoolYear::activeFor($request->user());
        abort_unless($activeYear !== null, 422, 'No active school year.');

        $data = $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'enrollment_ids' => ['required', 'array', 'min:1'],
            'enrollment_ids.*' => ['integer', 'exists:student_enrollments,id'],
            // Blank target_section_id = the promoting teacher's own class.
            'target_section_id' => ['nullable', 'exists:sections,id'],
            'target_name' => ['required_without:target_section_id', 'nullable', 'string', 'max:50'],
        ], [], ['enrollment_ids' => 'learners', 'target_name' => 'new class name']);

        // Only the adviser of the source class may move it up, and only from a closed year.
        $source = Section::with('gradeLevel')->findOrFail($data['section_id']);
        abort_unless($source->adviser_id === $teacher->id, 403, 'You are not the adviser of that class.');
        abort_unless($source->school_year_id !== $activeYear->id, 422, 'That class is already in the active school year.');

        // Selected enrollments must actually belong to the source section.
        $ids = StudentEnrollment::where('section_id', $source->id)
            ->whereIn('id', $data['enrollment_ids'])
            ->pluck('id')->all();
        abort_unless(count($ids) > 0, 422, 'None of the selected learners belong to that class.');

        if ($source->gradeLevel->is_graduating) {
            // No next grade — the selected learners graduate instead.
            $result = $this->promotion->promoteSelected($ids, $source, $request->user());

            return redirect()->route('teacher.promotion.index')
                ->with('success', "{$result['graduated']} learner(s) marked graduated.");
        }

        $nextGrade = $source->gradeLevel->nextGrade();
        abort_unless($nextGrade !== null, 422, 'No next grade level exists for this class.');

        if (! empty($data['target_section_id'])) {
            // Another adviser's class: the learners' new adviser is whoever
            // advises the chosen section — no admin needed to "assign" one.
            $target = Section::with('adviser')->findOrFail($data['target_section_id']);
            abort_unless($target->school_year_id === $activeYear->id, 422, 'The destination class is not in the active school year.');
            abort_unless($target->grade_level_id === $nextGrade->id, 422, 'The destination class is not the next grade level.');
        } else {
            // The adviser follows the class: their own advisory in the active
            // year, created here when it does not exist yet.
            $target = Section::firstOrCreate(
                [
                    'school_year_id' => $activeYear->id,
                    'grade_level_id' => $nextGrade->id,
                    'adviser_id' => $teacher->id,
                ],
                ['name' => trim((string) $data['target_name'])]
            );
        }

        $result = $this->promotion->promoteSelected($ids, $target->load('schoolYear'), $request->user());

        $adviserNote = $target->adviser_id === $teacher->id
            ? 'you stay their adviser'
            : 'new adviser: '.($target->adviser?->full_name ?? 'none yet');

        return redirect()->route('teacher.promotion.index', ['section_id' => $source->id])
            ->with('success', "Moved up into {$nextGrade->name} — {$target->name} (SY {$activeYear->name}, {$adviserNote}): "
                ."{$result['promoted']} promoted, {$result['skipped']} already enrolled this year.");
    }

    /** The teacher's advisory sections from non-active years that still hold active learners. */
    private function promotableSources(int $teacherId, ?SchoolYear $activeYear)
    {
        return Section::with(['gradeLevel', 'schoolYear'])
            ->withCount(['activeEnrollments as learners_count'])
            ->where('adviser_id', $teacherId)
            ->when($activeYear, fn ($q) => $q->where('school_year_id', '!=', $activeYear->id))
            ->having('learners_count', '>', 0)
            ->orderByDesc('school_year_id')
            ->get();
    }
}
