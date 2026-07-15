<?php

namespace Database\Seeders;

use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use App\Models\Teacher;
use App\Services\SchoolCalendarService;
use Illuminate\Database\Seeder;

/**
 * Creates the NEXT (inactive) school year with Grade 8 sections so the
 * end-of-year promotion flow can be demonstrated: Grade 7 (2025-2026)
 * → Grade 8 (2026-2027).
 */
class SecondYearSeeder extends Seeder
{
    public function run(): void
    {
        $sy = SchoolYear::updateOrCreate(
            ['name' => '2026-2027'],
            ['start_date' => '2026-06-15', 'end_date' => '2027-03-31', 'is_active' => false, 'status' => SchoolYear::STATUS_OPEN]
        );

        app(SchoolCalendarService::class)->generate($sy);

        $g8 = GradeLevel::where('code', 'G8')->first();
        $adviser = Teacher::first();

        if ($g8) {
            Section::updateOrCreate(
                ['school_year_id' => $sy->id, 'grade_level_id' => $g8->id, 'name' => 'Rizal'],
                ['adviser_id' => $adviser?->id, 'room' => 'Room 201', 'capacity' => 45]
            );
        }
    }
}
