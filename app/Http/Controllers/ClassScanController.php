<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Student;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Public, key-gated attendance scanner. Whoever the teacher assigns to scan
 * enters the class QR key to unlock scanning — no login required. The key is
 * the credential and is only valid while the class session is active.
 */
class ClassScanController extends Controller
{
    private const SESSION_KEY = 'class_scan_session_id';

    public function __construct(private readonly AttendanceService $attendance) {}

    /** Key-entry screen. */
    public function enter()
    {
        return view('class-sessions.scan-enter');
    }

    /** Validate the key and unlock scanning for that session. */
    public function unlock(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'qr_key' => ['required', 'string', 'max:12'],
        ]);

        $session = ClassSession::withoutGlobalScopes()
            ->active()
            ->where('qr_key', strtoupper(trim($data['qr_key'])))
            ->first();

        if (! $session) {
            return back()->withInput()->with('error', 'That key is not valid or the class has ended.');
        }

        $request->session()->put(self::SESSION_KEY, $session->id);

        return redirect()->route('class-scan.show');
    }

    /** The camera scanning screen for the unlocked session. */
    public function show(Request $request)
    {
        $session = $this->activeSession($request);
        if (! $session) {
            return redirect()->route('class-scan.enter')->with('error', 'Enter the class key to start scanning.');
        }

        $session->load(['section.gradeLevel', 'subject']);

        return view('class-sessions.scan', compact('session'));
    }

    /** Mark a scanned learner present for the unlocked session. */
    public function checkIn(Request $request): JsonResponse
    {
        $session = $this->activeSession($request);
        if (! $session) {
            return response()->json(['ok' => false, 'message' => 'Session ended. Re-enter the class key.'], 422);
        }

        $data = $request->validate(['token' => ['required', 'string']]);

        $student = Student::withoutGlobalScopes()->where('qr_token', $data['token'])->first();
        if (! $student) {
            return response()->json(['ok' => false, 'message' => 'Unknown QR code.'], 404);
        }

        $result = $this->attendance->markPresentForSession($session, $student);

        return response()->json($result, $result['ok'] ? 200 : 422);
    }

    /** Leave the scanning session. */
    public function exit(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('class-scan.enter');
    }

    /** Resolve the unlocked, still-active session from the browser session. */
    private function activeSession(Request $request): ?ClassSession
    {
        $id = $request->session()->get(self::SESSION_KEY);
        if (! $id) {
            return null;
        }

        $session = ClassSession::withoutGlobalScopes()->find($id);

        if (! $session || ! $session->isActive()) {
            $request->session()->forget(self::SESSION_KEY);

            return null;
        }

        return $session;
    }
}
