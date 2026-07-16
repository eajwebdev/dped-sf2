<?php

namespace Database\Seeders;

use App\Models\SchoolYear;
use App\Services\SchoolCalendarService;
use Illuminate\Database\Seeder;

/**
 * Creates the 2026-2027 school year with its class-day calendar. Sections are
 * teacher-made now (advisers create their own classes), so none are seeded.
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
    }
}
