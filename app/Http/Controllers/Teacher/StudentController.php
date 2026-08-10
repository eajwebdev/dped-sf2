<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Imports\StudentsImport;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;

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
        $advisorySections = $this->advisorySections($request->user());
        $selectedSectionId = $request->filled('section_id') ? (int) $request->integer('section_id') : null;
        $needsInfo = $request->boolean('needs_info');

        if ($selectedSectionId && ! $advisorySections->contains('id', $selectedSectionId)) {
            $selectedSectionId = null;
        }

        $advisorySectionIds = $advisorySections->pluck('id');

        $students = Student::query()
            ->with(['currentEnrollment.section.gradeLevel'])
            ->whereHas('enrollments', fn ($enrollment) => $enrollment->whereIn('section_id', $advisorySectionIds))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('lrn', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%");
                });
            })
            ->when($selectedSectionId, fn ($q) => $q->whereHas('enrollments', fn ($enrollment) => $enrollment->where('section_id', $selectedSectionId)))
            ->when($needsInfo, fn ($q) => $q->where(function ($missing) {
                $missing->whereNull('lrn')
                    ->orWhere('lrn', '')
                    ->orWhereNull('birthdate')
                    ->orWhereNull('guardian_contact')
                    ->orWhere('guardian_contact', '')
                    ->orWhere(function ($address) {
                        $address->where(fn ($single) => $single->whereNull('address')->orWhere('address', ''))
                            ->where(fn ($barangay) => $barangay->whereNull('address_barangay')->orWhere('address_barangay', ''));
                    });
            }))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->get('status')))
            ->orderBy('last_name')->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        $gradeLevels = GradeLevel::orderBy('id')->get();

        return view('teacher.students.index', compact('students', 'search', 'advisorySections', 'gradeLevels', 'selectedSectionId', 'needsInfo'));
    }

    public function create()
    {
        return $this->index(request())->with('openModal', 'create');
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        $request->validate(['section_id' => ['nullable', 'integer']]);

        // school_id is stamped automatically by the BelongsToSchool trait.
        $student = Student::create($request->safe()->except('photo'));
        $this->audit->created($student, "Student {$student->last_name} ({$student->lrn}) created");

        // A teacher's roster is their advisory class, so enroll straight into it —
        // an unenrolled learner would show up in no attendance sheet and no SF2.
        $section = $this->sectionForNewStudent($request);

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

        return redirect()->route('teacher.students.index', ['section_id' => $section->id])
            ->with('success', "Student added and enrolled into {$section->gradeLevel?->name} — {$section->name}.");
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'section_id' => ['required', 'integer'],
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $section = $this->advisorySections($request->user())
            ->firstWhere('id', (int) $request->integer('section_id'));

        if (! $section) {
            throw ValidationException::withMessages([
                'section_id' => 'Choose one of your advisory sections in the active school year.',
            ]);
        }

        $import = new StudentsImport($section, $this->enrollment);

        try {
            Excel::import($import, $request->file('file'));
        } catch (ExcelValidationException $e) {
            $count = count($e->failures());

            return back()->withInput()->with('error', "Import stopped: {$count} row(s) failed validation. First name, last name, and gender are required; LRN and other fields may be blank.");
        }

        $this->audit->log('import', $section, "Teacher imported {$import->imported} student(s), enrolled {$import->enrolled}, skipped {$import->skipped} duplicate LRN row(s), and skipped {$import->enrollmentSkipped} existing enrollment(s)");

        return back()->with('success', "Imported {$import->imported} new student(s) and enrolled {$import->enrolled} learner(s) into {$section->gradeLevel?->name} - {$section->name}. Skipped {$import->enrollmentSkipped} already-enrolled learner(s).");
    }

    public function importTemplate()
    {
        $headings = StudentsImport::templateHeadings();

        return Excel::download(new class($headings) implements FromArray, WithHeadings
        {
            public function __construct(private array $headings) {}

            public function array(): array
            {
                return [StudentsImport::templateSampleRow()];
            }

            public function headings(): array
            {
                return $this->headings;
            }
        }, 'students-import-template.xlsx');
    }

    /** The teacher's advisory section in the active school year, if any. */
    private function adviserSection(User $user): ?Section
    {
        return $this->advisorySections($user)->first();
    }

    private function sectionForNewStudent(Request $request): ?Section
    {
        $sections = $this->advisorySections($request->user());
        $selected = $request->filled('section_id')
            ? $sections->firstWhere('id', (int) $request->integer('section_id'))
            : null;

        return $selected ?? $sections->first();
    }

    /** The teacher's advisory sections in the active school year. */
    private function advisorySections(User $user): Collection
    {
        $teacherId = $user->teacher?->id;
        $activeYear = SchoolYear::activeFor($user);

        if (! $teacherId || ! $activeYear) {
            return collect();
        }

        return Section::with(['gradeLevel', 'schoolYear'])
            ->withCount(['activeEnrollments as learners_count'])
            ->where('adviser_id', $teacherId)
            ->where('school_year_id', $activeYear->id)
            ->orderBy('id')
            ->get();
    }

    public function edit(Student $student)
    {
        $this->authorizeStudentRecord(request(), $student);

        return $this->index(request())->with(['openModal' => 'edit', 'editModel' => $student]);
    }

    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        $this->authorizeStudentRecord($request, $student);

        $original = $student->getOriginal();
        $student->update($request->safe()->except('photo'));
        $this->audit->updated($student, $original);

        return redirect()->route('teacher.students.index')->with('success', 'Student updated.');
    }

    public function destroy(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudentRecord($request, $student);

        $name = $student->full_name;
        $student->delete();
        $this->audit->deleted($student, "Student {$name} deleted");

        return redirect()->route('teacher.students.index')->with('success', 'Student deleted.');
    }

    private function authorizeStudentRecord(Request $request, Student $student): void
    {
        $sectionIds = $this->advisorySections($request->user())->pluck('id');

        abort_unless(
            $sectionIds->isNotEmpty()
                && $student->enrollments()->whereIn('section_id', $sectionIds)->exists(),
            403,
            'Student records are limited to your advisory learners.'
        );
    }
}
