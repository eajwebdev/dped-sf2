<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\Student;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * School-scoped student management for teachers. The BelongsToSchool global
 * scope on Student restricts every query (and route-model binding) to the
 * teacher's own school, so no explicit school filtering is needed here.
 */
class StudentController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Request $request)
    {
        $search = trim((string) $request->get('q'));

        $students = Student::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('lrn', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->get('status')))
            ->orderBy('last_name')->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('teacher.students.index', compact('students', 'search'));
    }

    public function create()
    {
        return $this->index(request())->with('openModal', 'create');
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        // school_id is stamped automatically by the BelongsToSchool trait.
        $student = Student::create($request->safe()->except('photo'));
        $this->audit->created($student, "Student {$student->last_name} ({$student->lrn}) created");

        return redirect()->route('teacher.students.index')->with('success', 'Student added.');
    }

    public function edit(Student $student)
    {
        return $this->index(request())->with(['openModal' => 'edit', 'editModel' => $student]);
    }

    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        $original = $student->getOriginal();
        $student->update($request->safe()->except('photo'));
        $this->audit->updated($student, $original);

        return redirect()->route('teacher.students.index')->with('success', 'Student updated.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $name = $student->full_name;
        $student->delete();
        $this->audit->deleted($student, "Student {$name} deleted");

        return redirect()->route('teacher.students.index')->with('success', 'Student deleted.');
    }
}
