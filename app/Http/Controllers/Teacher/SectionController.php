<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
            'name' => ['required', 'string', 'max:50'],
            'grade_level_id' => ['required', 'exists:grade_levels,id'],
            'room' => ['nullable', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:200'],
        ], [], ['grade_level_id' => 'grade level']);

        // The only gate: that grade + section name must not already exist in the
        // teacher's school for the active year. Checked manually so the error can
        // actually say WHICH class exists instead of "name has already been taken".
        $existing = Section::query()
            ->with(['gradeLevel', 'adviser'])
            ->where('school_year_id', $activeYear->id)
            ->where('grade_level_id', $data['grade_level_id'])
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($data['name']))])
            ->when($request->user()->school_id, fn ($q, $s) => $q->where('school_id', $s))
            ->first();

        if ($existing) {
            return back()->withInput()->withErrors([
                'name' => "{$existing->gradeLevel?->name} — {$existing->name} already exists for SY {$activeYear->name}"
                    .($existing->adviser ? " (adviser: {$existing->adviser->full_name})" : '').'.',
            ]);
        }

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
