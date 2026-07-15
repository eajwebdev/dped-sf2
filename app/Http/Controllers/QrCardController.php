<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Student;
use App\Services\AttendanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;

class QrCardController extends Controller
{
    public function __construct(private readonly AttendanceService $attendance) {}

    /** Download a printable PDF of QR ID cards for every active learner in a section. */
    public function section(Request $request, Section $section)
    {
        $this->authorizeSection($request, $section);

        $section->load(['gradeLevel', 'schoolYear', 'adviser']);
        $roster = $this->attendance->roster($section);

        abort_if($roster->isEmpty(), 404, 'No active learners in this section.');

        $cards = $roster->map(fn ($enrollment) => [
            'name' => $enrollment->student->full_name,
            'lrn' => $enrollment->student->lrn,
            'qr' => $this->qrDataUri($enrollment->student->qr_token),
        ]);

        $pdf = Pdf::loadView('qr.cards-pdf', [
            'cards' => $cards,
            'section' => $section,
        ])->setPaper('a4', 'portrait');

        $filename = sprintf('qr-ids-%s-%s.pdf',
            str($section->gradeLevel->name)->slug(),
            str($section->name)->slug(),
        );

        return $pdf->download($filename);
    }

    /** Download a single learner's QR ID card. */
    public function student(Request $request, Student $student)
    {
        $enrollment = $student->currentEnrollment()->with(['section.gradeLevel', 'section.schoolYear'])->first();
        abort_unless($enrollment && $enrollment->section, 404, 'Learner has no enrollment in the active school year.');

        $this->authorizeSection($request, $enrollment->section);

        $pdf = Pdf::loadView('qr.cards-pdf', [
            'cards' => collect([[
                'name' => $student->full_name,
                'lrn' => $student->lrn,
                'qr' => $this->qrDataUri($student->qr_token),
            ]]),
            'section' => $enrollment->section,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('qr-id-'.str($student->last_name)->slug().'.pdf');
    }

    private function qrDataUri(string $token): string
    {
        return (new Builder(
            writer: new PngWriter(),
            data: $token,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 260,
            margin: 8,
        ))->build()->getDataUri();
    }

    private function authorizeSection(Request $request, Section $section): void
    {
        $user = $request->user();
        abort_unless(
            $user->isAdmin() || $user->accessibleSections()->whereKey($section->id)->exists(),
            403
        );
    }
}
