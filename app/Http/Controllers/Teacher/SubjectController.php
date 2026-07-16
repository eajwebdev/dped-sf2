<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubjectRequest;
use App\Models\GradeLevel;
use App\Models\Subject;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;

/**
 * School-scoped subject catalogue for teachers. Subjects are stamped with the
 * teacher's school by the BelongsToSchool trait and read back through the same
 * global scope.
 */
class SubjectController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index()
    {
        $subjects = Subject::with('gradeLevel')->orderBy('name')->paginate(20);
        $gradeLevels = GradeLevel::orderBy('level_order')->get();

        return view('teacher.subjects.index', compact('subjects', 'gradeLevels'));
    }

    public function create()
    {
        return $this->index()->with('openModal', 'create');
    }

    public function store(SubjectRequest $request): RedirectResponse
    {
        $subject = Subject::create($request->validated());
        $this->audit->created($subject);

        return redirect()->route('teacher.subjects.index')->with('success', "{$subject->name} added.");
    }

    public function edit(Subject $subject)
    {
        return $this->index()->with(['openModal' => 'edit', 'editModel' => $subject]);
    }

    public function update(SubjectRequest $request, Subject $subject): RedirectResponse
    {
        $original = $subject->getOriginal();
        $subject->update($request->validated());
        $this->audit->updated($subject, $original);

        return redirect()->route('teacher.subjects.index')->with('success', "{$subject->name} updated.");
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $name = $subject->name;
        $subject->delete();
        $this->audit->deleted($subject);

        return redirect()->route('teacher.subjects.index')->with('success', "{$name} deleted.");
    }
}
