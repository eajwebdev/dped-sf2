<?php

namespace App\Services;

use App\Models\Section;
use App\Models\StudentEnrollment;
use App\Models\Textbook;
use Illuminate\Support\Collection;

/**
 * Builds the DepEd School Form 3 (SF3) — Books Issued and Returned — for one
 * section: every learner ever on the register (males A–Z, then females A–Z),
 * one column pair (issued / returned) per textbook title.
 *
 * The official sheet fits 8 titles across; more than 8 chunks into additional
 * pages, mirroring guideline 5 ("additional copies of this form may be used").
 */
class Sf3ReportService
{
    /** Book columns per printed page, fixed by the official layout. */
    public const BOOKS_PER_PAGE = 8;

    public function build(Section $section): array
    {
        $section->loadMissing(['gradeLevel', 'adviser', 'schoolYear', 'school']);

        $books = Textbook::where('section_id', $section->id)
            ->orderBy('sort')->orderBy('id')
            ->get();

        $enrollments = StudentEnrollment::with([
            'student',
            'textbookIssuances' => fn ($q) => $q->whereIn('textbook_id', $books->pluck('id')),
        ])->where('section_id', $section->id)->get();

        $males = $this->rows($this->sorted($enrollments, 'Male'), $books);
        $females = $this->rows($this->sorted($enrollments, 'Female'), $books);

        return [
            'section' => $section,
            'schoolYear' => $section->schoolYear,
            'school' => $section->school,
            'adviser' => $section->adviser?->full_name,
            'books' => $books,
            'bookPages' => $books->isEmpty() ? collect([collect()]) : $books->chunk(self::BOOKS_PER_PAGE),
            'males' => $males,
            'females' => $females,
            'totals' => [
                'male' => $this->totals($males, $books),
                'female' => $this->totals($females, $books),
                'all' => $this->totals(array_merge($males, $females), $books),
            ],
        ];
    }

    private function sorted(Collection $enrollments, string $gender): Collection
    {
        return $enrollments
            ->filter(fn ($e) => $e->student?->gender === $gender)
            ->sortBy(fn ($e) => mb_strtolower($e->student->last_name.' '.$e->student->first_name.' '.$e->student->middle_name))
            ->values();
    }

    /** @return array<int, array<string, mixed>> */
    private function rows(Collection $enrollments, Collection $books): array
    {
        $rows = [];
        $no = 1;

        foreach ($enrollments as $enrollment) {
            $issuances = $enrollment->textbookIssuances->keyBy('textbook_id');
            $cells = [];
            $remarks = [];

            foreach ($books as $book) {
                $issue = $issuances->get($book->id);

                $cells[$book->id] = [
                    'issued' => $issue?->issued_at?->format('m/d/y'),
                    // A lost copy prints its code where the return date would go.
                    'returned' => $issue?->returned_at?->format('m/d/y') ?? $issue?->return_code,
                ];

                if ($issue?->action_code) {
                    $remarks[] = trim($issue->action_code.' '.($issue->remarks ?? ''));
                } elseif (filled($issue?->remarks)) {
                    $remarks[] = $issue->remarks;
                }
            }

            $rows[] = [
                'no' => $no++,
                'name' => $this->registerName($enrollment->student),
                'cells' => $cells,
                'remarks' => implode('; ', array_unique($remarks)),
            ];
        }

        return $rows;
    }

    private function registerName($student): string
    {
        $last = trim($student->last_name.' '.($student->suffix ?? ''));

        return trim(collect([$last, $student->first_name, $student->middle_name])
            ->filter()
            ->implode(', '));
    }

    /**
     * The TOTAL rows: learners in the block, and per-book copies issued and
     * returned (a lost-book code is not a return).
     *
     * @return array{learners:int, issued:array<int,int>, returned:array<int,int>, issued_total:int, returned_total:int}
     */
    private function totals(array $rows, Collection $books): array
    {
        $issued = [];
        $returned = [];

        foreach ($books as $book) {
            $issued[$book->id] = 0;
            $returned[$book->id] = 0;

            foreach ($rows as $row) {
                $cell = $row['cells'][$book->id];
                if ($cell['issued'] !== null) {
                    $issued[$book->id]++;
                }
                if ($cell['returned'] !== null && ! isset(\App\Models\TextbookIssuance::RETURN_CODES[$cell['returned']])) {
                    $returned[$book->id]++;
                }
            }
        }

        return [
            'learners' => count($rows),
            'issued' => $issued,
            'returned' => $returned,
            'issued_total' => array_sum($issued),
            'returned_total' => array_sum($returned),
        ];
    }
}
