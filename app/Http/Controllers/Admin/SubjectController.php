<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubjectRequest;
use App\Models\GradeLevel;
use App\Models\Subject;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;

class SubjectController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index()
    {
        $subjects = Subject::with('gradeLevel')->orderBy('name')->paginate(20);
        $gradeLevels = GradeLevel::orderBy('level_order')->get();

        return view('admin.subjects.index', compact('subjects', 'gradeLevels'));
    }

    public function create()
    {
        return $this->index()->with('openModal', 'create');
    }

    public function store(SubjectRequest $request): RedirectResponse
    {
        $subject = Subject::create($request->validated());
        $this->audit->created($subject);

        return redirect()->route('admin.subjects.index')->with('success', "{$subject->name} created.");
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

        return redirect()->route('admin.subjects.index')->with('success', "{$subject->name} updated.");
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $this->authorize('delete', $subject);
        $name = $subject->name;
        $subject->delete();
        $this->audit->deleted($subject);

        return redirect()->route('admin.subjects.index')->with('success', "{$name} deleted.");
    }
}
