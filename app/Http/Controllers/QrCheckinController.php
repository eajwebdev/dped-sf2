<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class QrCheckinController extends Controller
{
    public function __construct(private readonly AttendanceService $attendance) {}

    public function scan(Request $request, Section $section)
    {
        $this->authorizeSection($request, $section);

        return view('attendance.scan', [
            'section' => $section->load(['gradeLevel', 'schoolYear']),
            'date' => $request->date('date') ?? Carbon::today(),
        ]);
    }

    public function checkIn(Request $request, Section $section): JsonResponse
    {
        $this->authorizeSection($request, $section);

        $data = $request->validate([
            'token' => ['required', 'string'],
            'date' => ['required', 'date'],
            'status' => ['nullable', 'in:present,late'],
        ]);

        $student = Student::where('qr_token', $data['token'])->first();
        if (! $student) {
            return response()->json(['ok' => false, 'message' => 'Unknown QR code.'], 404);
        }

        $enrollment = StudentEnrollment::where('student_id', $student->id)
            ->where('section_id', $section->id)
            ->whereIn('status', ['enrolled', 'transferred_in'])
            ->first();

        if (! $enrollment) {
            return response()->json(['ok' => false, 'message' => "{$student->full_name} is not enrolled in this section."], 422);
        }

        $result = $this->attendance->save($request->user(), $section, Carbon::parse($data['date']), [
            ['enrollment_id' => $enrollment->id, 'status' => $data['status'] ?? 'present', 'remarks' => 'QR check-in'],
        ]);

        if (! empty($result['errors'])) {
            return response()->json(['ok' => false, 'message' => $result['errors'][0]], 422);
        }

        return response()->json([
            'ok' => true,
            'name' => $student->full_name,
            'status' => $data['status'] ?? 'present',
        ]);
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
