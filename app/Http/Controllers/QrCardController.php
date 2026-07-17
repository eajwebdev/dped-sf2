<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Student;
use App\Services\AttendanceService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class QrCardController extends Controller
{
    private const NAVY = [9, 20, 61];      // #09143D

    private const PINK = [252, 15, 93];    // #FC0F5D

    public function __construct(private readonly AttendanceService $attendance) {}

    /** Download every active learner's QR ID card in a section as a ZIP of PNGs. */
    public function section(Request $request, Section $section): BinaryFileResponse
    {
        $this->authorizeSection($request, $section);

        $section->load(['gradeLevel', 'schoolYear', 'school']);
        $roster = $this->attendance->roster($section);

        abort_if($roster->isEmpty(), 404, 'No active learners in this section.');

        $sectionLabel = $section->gradeLevel->name.' — '.$section->name;
        $schoolName = $section->school?->name ?? config('app.name');

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

            $zip->addFromString($name.'.png', $this->cardPng($student, $sectionLabel, $schoolName));
        }
        $zip->close();

        $filename = sprintf('qr-ids-%s-%s.zip',
            str($section->gradeLevel->name)->slug(),
            str($section->name)->slug(),
        );

        return response()->download($tmp, $filename, ['Content-Type' => 'application/zip'])
            ->deleteFileAfterSend(true);
    }

    /** Download a single learner's QR ID card as a PNG. */
    public function student(Request $request, Student $student): Response
    {
        $enrollment = $student->currentEnrollment()->with(['section.gradeLevel', 'section.schoolYear', 'section.school'])->first();

        // With an enrollment the section gates access; without one the token is
        // still printable (the QR is static) — the Student global scope already
        // limits route binding to the caller's own school.
        $sectionLabel = null;
        $schoolName = config('app.name');
        if ($enrollment && $enrollment->section) {
            $this->authorizeSection($request, $enrollment->section);
            $sectionLabel = $enrollment->section->gradeLevel->name.' — '.$enrollment->section->name;
            $schoolName = $enrollment->section->school?->name ?? $schoolName;
        }

        $filename = 'qr-'.(str($student->last_name.'-'.$student->first_name)->slug() ?: 'student-'.$student->id).'.png';

        return response($this->cardPng($student, $sectionLabel, $schoolName), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * A branded ID card: navy header with the school name, navy-on-white QR,
     * learner name, LRN, and section, finished with a pink accent bar.
     */
    private function cardPng(Student $student, ?string $sectionLabel, string $schoolName): string
    {
        $qr = imagecreatefromstring(
            (new Builder(
                writer: new PngWriter,
                data: $student->ensureQrToken(),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 340,
                margin: 0,
                foregroundColor: new Color(...self::NAVY),
            ))->build()->getString()
        );

        $w = 480;
        $h = 640;
        $card = imagecreatetruecolor($w, $h);
        imagesavealpha($card, true);
        imagealphablending($card, true);

        $white = imagecolorallocate($card, 255, 255, 255);
        $navy = imagecolorallocate($card, ...self::NAVY);
        $pink = imagecolorallocate($card, ...self::PINK);
        $gray = imagecolorallocate($card, 100, 116, 139);

        // Card body + navy header band.
        imagefilledrectangle($card, 0, 0, $w, $h, $white);
        imagefilledrectangle($card, 0, 0, $w, 96, $navy);
        imagefilledrectangle($card, 0, $h - 12, $w, $h, $pink);

        $bold = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
        $sans = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');

        // Header: school name (auto-shrunk to fit) + eyebrow.
        $this->centeredText($card, strtoupper($schoolName), $bold, 17, $white, $w, 44, 11);
        $this->centeredText($card, 'QR ATTENDANCE ID', $sans, 10, $pink, $w, 74, 8);

        // QR block.
        imagecopy($card, $qr, (int) (($w - 340) / 2), 126, 0, 0, 340, 340);
        imagedestroy($qr);

        // Thin pink rule between QR and name.
        imagefilledrectangle($card, (int) ($w / 2) - 28, 484, (int) ($w / 2) + 28, 486, $pink);

        // Learner identity.
        $this->centeredText($card, $student->full_name, $bold, 20, $navy, $w, 526, 13);
        $this->centeredText($card, 'LRN '.$student->lrn, $sans, 12, $gray, $w, 556, 9);
        if ($sectionLabel) {
            $this->centeredText($card, $sectionLabel, $sans, 12, $gray, $w, 584, 9);
        }

        ob_start();
        imagepng($card);
        imagedestroy($card);

        return ob_get_clean();
    }

    /** Draw centered text, shrinking the font until it fits the card width. */
    private function centeredText($img, string $text, string $font, int $size, int $color, int $width, int $baseline, int $minSize): void
    {
        $max = $width - 40;
        while ($size > $minSize) {
            $box = imagettfbbox($size, 0, $font, $text);
            if (($box[2] - $box[0]) <= $max) {
                break;
            }
            $size--;
        }
        // Ellipsize if it still overflows at the minimum size.
        $box = imagettfbbox($size, 0, $font, $text);
        while (($box[2] - $box[0]) > $max && mb_strlen($text) > 4) {
            $text = mb_substr($text, 0, -2).'…';
            $box = imagettfbbox($size, 0, $font, $text);
        }

        imagettftext($img, $size, 0, (int) (($width - ($box[2] - $box[0])) / 2), $baseline, $color, $font, $text);
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
