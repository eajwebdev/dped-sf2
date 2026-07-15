<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\Student;
use App\Services\AuditLogger;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly QrCodeService $qr,
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

        return view('admin.students.index', compact('students', 'search'));
    }

    public function create()
    {
        return view('admin.students.create', ['student' => new Student(['status' => 'active'])]);
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('photo');
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('students', 'public');
        }

        $student = Student::create($data);
        $this->audit->created($student, "Student {$student->last_name} ({$student->lrn}) created");

        return redirect()->route('admin.students.show', $student)->with('success', 'Student created.');
    }

    public function show(Student $student)
    {
        $this->authorize('view', $student);

        $student->load([
            'enrollments' => fn ($q) => $q->with(['schoolYear', 'gradeLevel', 'section'])->latest('enrollment_date'),
        ]);

        $qrDataUri = $this->qr->dataUri($student->qr_token ?? (string) $student->id, 180);

        return view('admin.students.show', compact('student', 'qrDataUri'));
    }

    public function edit(Student $student)
    {
        return view('admin.students.edit', compact('student'));
    }

    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        $original = $student->getOriginal();
        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            if ($student->photo_path) {
                Storage::disk('public')->delete($student->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('students', 'public');
        }

        $student->update($data);
        $this->audit->updated($student, $original);

        return redirect()->route('admin.students.show', $student)->with('success', 'Student updated.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorize('delete', $student);

        if ($student->photo_path) {
            Storage::disk('public')->delete($student->photo_path);
        }
        $name = $student->full_name;
        $student->delete();
        $this->audit->deleted($student, "Student {$name} deleted");

        return redirect()->route('admin.students.index')->with('success', 'Student deleted.');
    }
}
