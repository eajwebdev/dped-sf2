<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendance) {}

    /** Landing: pick one of the user's accessible sections + a date. */
    public function index(Request $request)
    {
        $sections = $request->user()->accessibleSections()
            ->with(['gradeLevel', 'schoolYear', 'adviser'])
            ->whereHas('schoolYear', fn ($q) => $q->where('is_active', true))
            ->orderBy('grade_level_id')->orderBy('name')
            ->get();

        return view('attendance.index', [
            'sections' => $sections,
            'date' => $request->date('date') ?? Carbon::today(),
        ]);
    }

    /** The marking grid for a section on a date. */
    public function sheet(Request $request, Section $section)
    {
        $this->authorizeSection($request, $section);

        $date = $request->date('date') ?? Carbon::today();
        $data = $this->attendance->sheet($request->user(), $section, $date);

        return view('attendance.sheet', $data + [
            'statuses' => $this->statusMeta(),
        ]);
    }

    /** Autosave endpoint (JSON). Persists a batch of marks. */
    public function save(Request $request, Section $section): JsonResponse
    {
        $this->authorizeSection($request, $section);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'marks' => ['required', 'array'],
            'marks.*.enrollment_id' => ['required', 'integer'],
            'marks.*.status' => ['nullable', 'string'],
            'marks.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $date = Carbon::parse($validated['date']);
        $result = $this->attendance->save($request->user(), $section, $date, $validated['marks']);

        $sheet = $this->attendance->sheet($request->user(), $section, $date);

        return response()->json([
            'saved' => $result['saved'],
            'errors' => $result['errors'],
            'summary' => $sheet['summary'],
            'savedAt' => now()->format('g:i:s A'),
        ], empty($result['errors']) ? 200 : 422);
    }

    /** Admin re-opens a locked date for editing. */
    public function unlock(Request $request, Section $section): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $date = Carbon::parse($request->validate(['date' => ['required', 'date']])['date']);
        $this->attendance->unlock($request->user(), $section, $date);

        return back()->with('success', "Attendance for {$date->format('M d, Y')} unlocked.");
    }

    private function authorizeSection(Request $request, Section $section): void
    {
        $user = $request->user();
        abort_unless(
            $user->isAdmin() || $user->accessibleSections()->whereKey($section->id)->exists(),
            403,
            'You are not assigned to this section.'
        );
    }

    /** @return array<string, array{label:string, key:string, class:string}> */
    private function statusMeta(): array
    {
        return [
            'present' => ['label' => 'Present', 'key' => 'P', 'class' => 'bg-emerald-500'],
            'absent' => ['label' => 'Absent', 'key' => 'A', 'class' => 'bg-red-500'],
            'late' => ['label' => 'Late', 'key' => 'L', 'class' => 'bg-amber-500'],
            'excused' => ['label' => 'Excused', 'key' => 'E', 'class' => 'bg-blue-500'],
            'half_day' => ['label' => 'Half day', 'key' => 'H', 'class' => 'bg-violet-500'],
        ];
    }
}
