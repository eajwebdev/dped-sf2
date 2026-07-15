<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GradeLevelRequest;
use App\Models\GradeLevel;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;

class GradeLevelController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index()
    {
        $gradeLevels = GradeLevel::withCount('sections')->orderBy('level_order')->paginate(20);

        return view('admin.grade-levels.index', compact('gradeLevels'));
    }

    public function create()
    {
        return $this->index()->with('openModal', 'create');
    }

    public function store(GradeLevelRequest $request): RedirectResponse
    {
        $grade = GradeLevel::create($request->validated());
        $this->audit->created($grade);

        return redirect()->route('admin.grade-levels.index')->with('success', "{$grade->name} created.");
    }

    public function edit(GradeLevel $gradeLevel)
    {
        return $this->index()->with(['openModal' => 'edit', 'editModel' => $gradeLevel]);
    }

    public function update(GradeLevelRequest $request, GradeLevel $gradeLevel): RedirectResponse
    {
        $original = $gradeLevel->getOriginal();
        $gradeLevel->update($request->validated());
        $this->audit->updated($gradeLevel, $original);

        return redirect()->route('admin.grade-levels.index')->with('success', "{$gradeLevel->name} updated.");
    }

    public function destroy(GradeLevel $gradeLevel): RedirectResponse
    {
        $this->authorize('delete', $gradeLevel);
        $name = $gradeLevel->name;
        $gradeLevel->delete();
        $this->audit->deleted($gradeLevel);

        return redirect()->route('admin.grade-levels.index')->with('success', "{$name} deleted.");
    }
}
