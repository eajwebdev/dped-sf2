<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * School-scoped student management for teachers. The BelongsToSchool global
 * scope on Student restricts every query (and route-model binding) to the
 * teacher's own school, so no explicit school filtering is needed here.
 */
class StudentController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly EnrollmentService $enrollment,
    ) {}

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

        // A teacher's roster is their advisory class, so enroll straight into it —
        // an unenrolled learner would show up in no attendance sheet and no SF2.
        $section = $this->adviserSection($request->user());

        if (! $section) {
            return redirect()->route('teacher.students.index')
                ->with('success', 'Student added, but not enrolled — you have no advisory class in the active school year.');
        }

        try {
            $this->enrollment->enroll($student, $section);
        } catch (ValidationException $e) {
            return redirect()->route('teacher.students.index')
                ->with('success', 'Student added, but not enrolled: '.collect($e->errors())->flatten()->first());
        }

        return redirect()->route('teacher.students.index')
            ->with('success', "Student added and enrolled into {$section->gradeLevel?->name} — {$section->name}.");
    }

    /** The teacher's advisory section in the active school year, if any. */
    private function adviserSection(User $user): ?Section
    {
        $teacherId = $user->teacher?->id;
        $activeYear = SchoolYear::activeFor($user);

        if (! $teacherId || ! $activeYear) {
            return null;
        }

        return Section::with('gradeLevel')
            ->where('adviser_id', $teacherId)
            ->where('school_year_id', $activeYear->id)
            ->orderBy('id')
            ->first();
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
