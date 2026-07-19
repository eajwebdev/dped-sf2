<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves an applicant's school ID photo to a reviewing administrator.
 *
 * These are identity documents, so they never sit on the public disk and are
 * never linked directly. Every read is authorised, scoped to the admin's own
 * school, and written to the audit trail — looking at someone's ID is itself
 * an action worth recording.
 */
class SchoolIdDocumentController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function show(Request $request, User $user): StreamedResponse
    {
        $admin = $request->user();

        abort_unless($admin->isAdmin(), 403);

        // A school's admin may only review applicants to their own school.
        // Platform admins (no school of their own) may review any.
        abort_if(
            $admin->school_id !== null && $admin->school_id !== $user->school_id,
            403,
            'This applicant belongs to another school.'
        );

        abort_unless($user->school_id_document_path, 404, 'No school ID was uploaded for this applicant.');

        $disk = Storage::disk('local');
        abort_unless($disk->exists($user->school_id_document_path), 404, 'The uploaded file is no longer available.');

        $this->audit->log('school_id_document_viewed', $user,
            "Reviewed the school ID uploaded by {$user->email}");

        // Inline so the admin sees it in the approval screen, with sniffing
        // disabled so the browser cannot be talked into treating it as HTML.
        return $disk->response($user->school_id_document_path, 'school-id.jpg', [
            'Content-Disposition' => 'inline; filename="school-id.jpg"',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self'",
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }
}
