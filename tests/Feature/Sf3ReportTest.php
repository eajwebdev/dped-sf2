<?php

namespace Tests\Feature;

use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\Teacher;
use App\Models\Textbook;
use App\Models\TextbookIssuance;
use App\Models\User;
use App\Services\Sf3ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sf3ReportTest extends TestCase
{
    use RefreshDatabase;

    private SchoolYear $year;

    private Section $section;

    private User $adviser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->year = SchoolYear::factory()->active()->create([
            'start_date' => '2025-06-01', 'end_date' => '2026-03-31',
        ]);
        $grade = GradeLevel::factory()->create(['level_order' => 8, 'code' => 'G8']);

        $this->adviser = User::factory()->create(['role' => 'teacher', 'is_active' => true, 'status' => User::STATUS_APPROVED]);
        $teacher = Teacher::factory()->create(['user_id' => $this->adviser->id]);

        $this->section = Section::factory()->create([
            'school_year_id' => $this->year->id,
            'grade_level_id' => $grade->id,
            'adviser_id' => $teacher->id,
        ]);
    }

    private function enroll(string $gender, array $student = []): StudentEnrollment
    {
        $s = Student::factory()->create($student + ['gender' => $gender]);

        return StudentEnrollment::create([
            'student_id' => $s->id,
            'school_year_id' => $this->year->id,
            'grade_level_id' => $this->section->grade_level_id,
            'section_id' => $this->section->id,
            'status' => 'enrolled',
            'promotion_status' => 'pending',
            'enrollment_date' => '2025-06-01',
        ]);
    }

    private function book(string $subject, string $title = 'Learner Module'): Textbook
    {
        return Textbook::create([
            'section_id' => $this->section->id,
            'subject_area' => $subject,
            'title' => $title,
        ]);
    }

    public function test_totals_count_issued_and_returned_copies_per_book_and_gender(): void
    {
        $m = $this->enroll('Male');
        $f = $this->enroll('Female');
        $book = $this->book('Mathematics 8');

        TextbookIssuance::create([
            'textbook_id' => $book->id, 'student_enrollment_id' => $m->id,
            'issued_at' => '2025-06-10', 'returned_at' => '2026-03-20',
        ]);
        TextbookIssuance::create([
            'textbook_id' => $book->id, 'student_enrollment_id' => $f->id,
            'issued_at' => '2025-06-10',
        ]);

        $data = app(Sf3ReportService::class)->build($this->section);

        $this->assertSame(1, $data['totals']['male']['issued'][$book->id]);
        $this->assertSame(1, $data['totals']['male']['returned'][$book->id]);
        $this->assertSame(1, $data['totals']['female']['issued'][$book->id]);
        $this->assertSame(0, $data['totals']['female']['returned'][$book->id]);
        $this->assertSame(2, $data['totals']['all']['issued_total']);
        $this->assertSame(1, $data['totals']['all']['returned_total']);
    }

    public function test_a_lost_book_prints_its_code_in_the_returned_cell_and_is_not_a_return(): void
    {
        $m = $this->enroll('Male');
        $book = $this->book('Science 8');

        TextbookIssuance::create([
            'textbook_id' => $book->id, 'student_enrollment_id' => $m->id,
            'issued_at' => '2025-06-10', 'return_code' => 'NEG', 'action_code' => 'PTL',
        ]);

        $data = app(Sf3ReportService::class)->build($this->section);
        $row = $data['males'][0];

        $this->assertSame('NEG', $row['cells'][$book->id]['returned']);
        $this->assertStringContainsString('PTL', $row['remarks']);
        $this->assertSame(0, $data['totals']['male']['returned'][$book->id]);
    }

    public function test_more_than_eight_books_chunk_onto_extra_pages(): void
    {
        $this->enroll('Male');
        foreach (range(1, 10) as $i) {
            $this->book("Subject {$i}");
        }

        $data = app(Sf3ReportService::class)->build($this->section);

        $this->assertSame(2, $data['bookPages']->count());
        $this->assertCount(8, $data['bookPages']->first());
        $this->assertCount(2, $data['bookPages']->last());
    }

    public function test_adviser_can_generate_the_sf3_pdf(): void
    {
        $m = $this->enroll('Male', ['last_name' => 'Dela Cruz', 'first_name' => 'Juan']);
        $book = $this->book('Filipino 8', 'Filipino sa Piling Larang');
        TextbookIssuance::create([
            'textbook_id' => $book->id, 'student_enrollment_id' => $m->id, 'issued_at' => '2025-06-10',
        ]);

        $response = $this->actingAs($this->adviser)->get(route('reports.sf3.show', $this->section));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_non_adviser_cannot_generate_or_manage(): void
    {
        $other = User::factory()->create(['role' => 'teacher', 'is_active' => true, 'status' => User::STATUS_APPROVED]);
        Teacher::factory()->create(['user_id' => $other->id]);

        $this->actingAs($other)->get(route('reports.sf3.show', $this->section))->assertForbidden();
        $this->actingAs($other)->get(route('books.index', $this->section))->assertForbidden();
        $this->actingAs($other)->post(route('books.store', $this->section), [
            'subject_area' => 'X', 'title' => 'Y',
        ])->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | Issuance management
    |--------------------------------------------------------------------------
    */

    public function test_the_issuance_screen_renders_with_books_and_roster(): void
    {
        $m = $this->enroll('Male', ['last_name' => 'Reyes', 'first_name' => 'Ana']);
        $book = $this->book('Mathematics 8', 'Math Learner Module');
        TextbookIssuance::create([
            'textbook_id' => $book->id, 'student_enrollment_id' => $m->id, 'issued_at' => '2025-06-10',
        ]);

        $this->actingAs($this->adviser)->get(route('books.index', $this->section))
            ->assertOk()
            ->assertSee('Math Learner Module')
            ->assertSee('Reyes, Ana')
            ->assertSee('Generate SF3');
    }

    public function test_adviser_can_add_a_book_and_issue_it_to_the_whole_class(): void
    {
        $this->enroll('Male');
        $this->enroll('Female');

        $this->actingAs($this->adviser)->post(route('books.store', $this->section), [
            'subject_area' => 'English 8', 'title' => 'Voyages in Communication',
        ])->assertRedirect();

        $book = Textbook::firstOrFail();

        $this->actingAs($this->adviser)->post(route('books.issue-all', [$this->section, $book]), [
            'issued_at' => '2025-06-15',
        ])->assertRedirect();

        $this->assertSame(2, TextbookIssuance::whereNotNull('issued_at')->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'textbooks_issued']);
    }

    public function test_issue_all_skips_learners_who_already_have_a_copy(): void
    {
        $m = $this->enroll('Male');
        $this->enroll('Female');
        $book = $this->book('AP 8');

        TextbookIssuance::create([
            'textbook_id' => $book->id, 'student_enrollment_id' => $m->id, 'issued_at' => '2025-06-01',
        ]);

        $this->actingAs($this->adviser)->post(route('books.issue-all', [$this->section, $book]), [
            'issued_at' => '2025-06-15',
        ])->assertRedirect();

        // The early copy keeps its original date.
        $this->assertSame('2025-06-01', $m->textbookIssuances()->first()->issued_at->toDateString());
        $this->assertSame(2, TextbookIssuance::count());
    }

    public function test_a_return_date_clears_any_lost_code(): void
    {
        $m = $this->enroll('Male');
        $book = $this->book('TLE 8');

        TextbookIssuance::create([
            'textbook_id' => $book->id, 'student_enrollment_id' => $m->id,
            'issued_at' => '2025-06-10', 'return_code' => 'FM',
        ]);

        $this->actingAs($this->adviser)->post(route('books.cell', $this->section), [
            'textbook_id' => $book->id,
            'student_enrollment_id' => $m->id,
            'issued_at' => '2025-06-10',
            'returned_at' => '2026-03-20',
            'return_code' => 'FM',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $issue = TextbookIssuance::firstOrFail();
        $this->assertSame('2026-03-20', $issue->returned_at->toDateString());
        $this->assertNull($issue->return_code);
    }

    public function test_a_cell_cannot_reference_another_sections_book_or_learner(): void
    {
        $otherSection = Section::factory()->create([
            'school_year_id' => $this->year->id,
            'grade_level_id' => $this->section->grade_level_id,
        ]);
        $foreignBook = Textbook::create([
            'section_id' => $otherSection->id, 'subject_area' => 'X', 'title' => 'Y',
        ]);
        $mine = $this->enroll('Male');

        $this->actingAs($this->adviser)->post(route('books.cell', $this->section), [
            'textbook_id' => $foreignBook->id,
            'student_enrollment_id' => $mine->id,
            'issued_at' => '2025-06-10',
        ])->assertSessionHasErrors('textbook_id');
    }

    public function test_a_book_with_issued_copies_cannot_be_deleted(): void
    {
        $m = $this->enroll('Male');
        $book = $this->book('MAPEH 8');
        TextbookIssuance::create([
            'textbook_id' => $book->id, 'student_enrollment_id' => $m->id, 'issued_at' => '2025-06-10',
        ]);

        $this->actingAs($this->adviser)
            ->delete(route('books.destroy', [$this->section, $book]))
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseHas('textbooks', ['id' => $book->id]);
    }
}
