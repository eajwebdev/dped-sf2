<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Student;
use App\Services\AttendanceService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class QrCardController extends Controller
{
    public function __construct(private readonly AttendanceService $attendance) {}

    /** Download every active learner's QR code in a section as a ZIP of PNG images. */
    public function section(Request $request, Section $section): BinaryFileResponse
    {
        $this->authorizeSection($request, $section);

        $section->load(['gradeLevel', 'schoolYear']);
        $roster = $this->attendance->roster($section);

        abort_if($roster->isEmpty(), 404, 'No active learners in this section.');

        $tmp = tempnam(sys_get_temp_dir(), 'qrzip');
        $zip = new ZipArchive;
        $zip->open($tmp, ZipArchive::OVERWRITE);

        $used = [];
        foreach ($roster as $enrollment) {
            $student = $enrollment->student;
            $base = (string) str($student->last_name.'-'.$student->first_name)->slug() ?: 'student-'.$student->id;

            // Guarantee unique filenames for same-named learners.
            $name = $base;
            $n = 1;
            while (isset($used[$name])) {
                $name = $base.'-'.(++$n);
            }
            $used[$name] = true;

            $zip->addFromString($name.'.png', $this->qrPng($student->qr_token, $student->full_name));
        }
        $zip->close();

        $filename = sprintf('qr-ids-%s-%s.zip',
            str($section->gradeLevel->name)->slug(),
            str($section->name)->slug(),
        );

        return response()->download($tmp, $filename, ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }

    /** Download a single learner's QR code as a labelled PNG image. */
    public function student(Request $request, Student $student): Response
    {
        $enrollment = $student->currentEnrollment()->with(['section.gradeLevel', 'section.schoolYear'])->first();
        abort_unless($enrollment && $enrollment->section, 404, 'Learner has no enrollment in the active school year.');

        $this->authorizeSection($request, $enrollment->section);

        $filename = 'qr-'.(str($student->last_name.'-'.$student->first_name)->slug() ?: 'student-'.$student->id).'.png';

        return response($this->qrPng($student->qr_token, $student->full_name), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /** Raw PNG bytes for a QR code with the learner's name printed underneath. */
    private function qrPng(string $token, string $label): string
    {
        return (new Builder(
            writer: new PngWriter,
            data: $token,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 320,
            margin: 12,
            labelText: $label,
        ))->build()->getString();
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
