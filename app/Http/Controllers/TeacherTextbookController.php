<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Models\Textbook;
use App\Models\TextbookIssuance;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The adviser's working screen behind SF3: the section's textbook titles and
 * the issue/return record for every learner.
 */
class TeacherTextbookController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function index(Request $request, Section $section)
    {
        $this->authorizeSection($request, $section);

        $section->load(['gradeLevel', 'schoolYear']);

        $books = Textbook::where('section_id', $section->id)
            ->withCount(['issuances as issued_count' => fn ($q) => $q->whereNotNull('issued_at')])
            ->orderBy('sort')->orderBy('id')
            ->get();

        $roster = StudentEnrollment::with(['student', 'textbookIssuances'])
            ->where('section_id', $section->id)
            ->get()
            ->sortBy(fn ($e) => ($e->student->gender === 'Male' ? '0' : '1')
                .mb_strtolower($e->student->last_name.' '.$e->student->first_name))
            ->values();

        return view('teacher.textbooks.index', [
            'section' => $section,
            'books' => $books,
            'roster' => $roster,
            'returnCodes' => TextbookIssuance::RETURN_CODES,
            'actionCodes' => TextbookIssuance::ACTION_CODES,
        ]);
    }

    public function storeBook(Request $request, Section $section): RedirectResponse
    {
        $this->authorizeSection($request, $section);

        $data = $request->validate([
            'subject_area' => ['required', 'string', 'max:80'],
            'title' => ['required', 'string', 'max:255'],
        ]);

        $book = Textbook::create($data + [
            'section_id' => $section->id,
            'sort' => (int) Textbook::where('section_id', $section->id)->max('sort') + 1,
        ]);

        $this->audit->created($book, "Textbook \"{$book->title}\" added to {$section->name}");

        return back()->with('success', "\"{$book->title}\" added.");
    }

    public function destroyBook(Request $request, Section $section, Textbook $book): RedirectResponse
    {
        $this->authorizeSection($request, $section);
        abort_unless($book->section_id === $section->id, 404);

        $issued = $book->issuances()->whereNotNull('issued_at')->count();
        if ($issued > 0) {
            return back()->with('error',
                "\"{$book->title}\" has {$issued} issued ".str('copy')->plural($issued).' on record — return them first, or the SF3 loses its history.');
        }

        $title = $book->title;
        $book->delete();
        $this->audit->deleted($book, "Textbook \"{$title}\" removed from {$section->name}");

        return back()->with('success', "\"{$title}\" removed.");
    }

    /** Hand one title to every learner who does not have a copy yet. */
    public function issueAll(Request $request, Section $section, Textbook $book): RedirectResponse
    {
        $this->authorizeSection($request, $section);
        abort_unless($book->section_id === $section->id, 404);

        $data = $request->validate(['issued_at' => ['required', 'date']]);

        $enrollmentIds = StudentEnrollment::where('section_id', $section->id)->pluck('id');
        $existing = $book->issuances()->pluck('student_enrollment_id');
        $count = 0;

        foreach ($enrollmentIds->diff($existing) as $enrollmentId) {
            TextbookIssuance::create([
                'textbook_id' => $book->id,
                'student_enrollment_id' => $enrollmentId,
                'issued_at' => $data['issued_at'],
            ]);
            $count++;
        }

        // Learners with a row but no issue date (e.g. cleared earlier) get one too.
        $count += $book->issuances()->whereNull('issued_at')
            ->update(['issued_at' => $data['issued_at']]);

        $this->audit->log('textbooks_issued', $book,
            "\"{$book->title}\" issued to {$count} learner(s) in {$section->name}");

        return back()->with('success', "\"{$book->title}\" issued to {$count} ".str('learner')->plural($count).'.');
    }

    /** Set or clear one learner's issue/return record for one title. */
    public function saveCell(Request $request, Section $section): RedirectResponse
    {
        $this->authorizeSection($request, $section);

        $data = $request->validate([
            'textbook_id' => ['required', Rule::exists('textbooks', 'id')->where('section_id', $section->id)],
            'student_enrollment_id' => ['required', Rule::exists('student_enrollments', 'id')->where('section_id', $section->id)],
            'issued_at' => ['nullable', 'date'],
            'returned_at' => ['nullable', 'date', 'after_or_equal:issued_at'],
            'return_code' => ['nullable', Rule::in(array_keys(TextbookIssuance::RETURN_CODES))],
            'action_code' => ['nullable', Rule::in(array_keys(TextbookIssuance::ACTION_CODES))],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        // A date and a lost-code are mutually exclusive in the same cell.
        if (! empty($data['returned_at'])) {
            $data['return_code'] = null;
        }

        TextbookIssuance::updateOrCreate(
            [
                'textbook_id' => $data['textbook_id'],
                'student_enrollment_id' => $data['student_enrollment_id'],
            ],
            collect($data)->except(['textbook_id', 'student_enrollment_id'])->all()
        );

        return back()->with('success', 'Record saved.');
    }

    private function authorizeSection(Request $request, Section $section): void
    {
        $user = $request->user();
        abort_unless(
            $user->isAdmin() || $user->advisorySections()->whereKey($section->id)->exists(),
            403,
            'Only the class adviser records book issuances for this section.'
        );
    }
}
