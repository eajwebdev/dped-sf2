<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Subject;
use App\Models\SubjectAssignment;
use App\Models\Teacher;
use App\Models\TeacherSubjectAssignment;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    /** Manage a section's subject offerings and their teachers. */
    public function index(Request $request, Section $section)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $section->load([
            'gradeLevel', 'schoolYear',
            'subjectAssignments.subject',
            'subjectAssignments.teacherAssignments.teacher',
        ]);

        $usedSubjectIds = $section->subjectAssignments->pluck('subject_id');

        // Subjects that fit this grade (or are general) and aren't offered yet.
        $availableSubjects = Subject::query()
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('grade_level_id')->orWhere('grade_level_id', $section->grade_level_id))
            ->whereNotIn('id', $usedSubjectIds)
            ->orderBy('name')->get();

        $teachers = Teacher::where('is_active', true)->orderBy('last_name')->get();

        return view('admin.assignments.index', compact('section', 'availableSubjects', 'teachers'));
    }

    public function storeSubject(Request $request, Section $section): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
        ]);

        $assignment = SubjectAssignment::firstOrCreate(
            ['section_id' => $section->id, 'subject_id' => $data['subject_id']],
            ['school_year_id' => $section->school_year_id, 'grade_level_id' => $section->grade_level_id],
        );
        $this->audit->created($assignment, 'Subject offering added to section');

        return back()->with('success', 'Subject added to section.');
    }

    public function destroySubject(Request $request, SubjectAssignment $subjectAssignment): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $this->audit->deleted($subjectAssignment, 'Subject offering removed');
        $subjectAssignment->delete(); // teacher links cascade

        return back()->with('success', 'Subject removed from section.');
    }

    public function assignTeacher(Request $request, SubjectAssignment $subjectAssignment): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'is_primary' => ['boolean'],
        ]);

        $link = TeacherSubjectAssignment::firstOrCreate(
            ['subject_assignment_id' => $subjectAssignment->id, 'teacher_id' => $data['teacher_id']],
            ['is_primary' => $request->boolean('is_primary')],
        );
        $this->audit->created($link, 'Teacher assigned to subject offering');

        return back()->with('success', 'Teacher assigned.');
    }

    public function unassignTeacher(Request $request, TeacherSubjectAssignment $teacherSubjectAssignment): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $this->audit->deleted($teacherSubjectAssignment, 'Teacher unassigned from subject offering');
        $teacherSubjectAssignment->delete();

        return back()->with('success', 'Teacher unassigned.');
    }
}
