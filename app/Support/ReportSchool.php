<?php

namespace App\Support;

use App\Models\School;
use App\Models\Section;

/**
 * Resolves which school a printed School Form belongs to, and which logo it
 * should carry.
 *
 * Every form header needs the same two facts (DepEd School ID and the school
 * seal), so the lookup lives here rather than being repeated — with slightly
 * different fallbacks — in each of the five report views.
 */
class ReportSchool
{
    /**
     * The school whose details head the form.
     *
     * A section is the authority when it carries a school. Otherwise fall back
     * to the adviser — the teacher the section belongs to — before the signed-in
     * user, because reports are also rendered from queues and the console where
     * there is no authenticated user at all.
     */
    public static function for(?Section $section): ?School
    {
        if (! $section) {
            return auth()->user()?->school;
        }

        $section->loadMissing(['school', 'adviser.school', 'adviser.user.school']);

        return $section->school
            ?? $section->adviser?->school
            ?? $section->adviser?->user?->school
            ?? auth()->user()?->school;
    }

    /**
     * Absolute path to the seal printed beside the DepEd logo: the school's own
     * upload when it has one, otherwise the bundled placeholder. DomPDF reads
     * from disk, so this is a filesystem path and never a URL.
     */
    public static function logoPath(?School $school): string
    {
        $path = $school?->logo_path;

        return $path && is_file(public_path($path))
            ? public_path($path)
            : public_path('logo.png');
    }
}
