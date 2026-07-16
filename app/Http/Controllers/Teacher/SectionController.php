<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Lets a teacher create their own advisory class for the active school year.
 * Without this a teacher with no advisory has no way to bootstrap a roster,
 * since adding a learner enrolls them into the adviser's section.
 */
class SectionController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function store(Request $request): RedirectResponse
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher !== null, 403, 'Your account is not linked to a teacher record.');

        $activeYear = SchoolYear::activeFor($request->user());

        if (! $activeYear) {
            return back()->with('error', 'No active school year — ask an administrator to activate one first.');
        }

        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:50',
                // Section names only need to be unique within a grade level for the year.
                Rule::unique('sections', 'name')
                    ->where(fn ($q) => $q->where('school_year_id', $activeYear->id)
                        ->where('grade_level_id', $request->integer('grade_level_id'))
                        ->whereNull('deleted_at')),
            ],
            'grade_level_id' => ['required', 'exists:grade_levels,id'],
            'room' => ['nullable', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:200'],
        ], [], ['grade_level_id' => 'grade level']);

        // school_id is stamped by the BelongsToSchool trait.
        $section = Section::create($data + [
            'school_year_id' => $activeYear->id,
            'adviser_id' => $teacher->id,
        ]);

        $this->audit->created($section, "Section {$section->name} created for SY {$activeYear->name}");

        return redirect()->route('teacher.dashboard')
            ->with('success', "Class {$section->gradeLevel?->name} — {$section->name} created. You are its adviser.");
    }
}
