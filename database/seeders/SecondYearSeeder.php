<?php

namespace Database\Seeders;

use App\Models\SchoolYear;
use App\Services\SchoolCalendarService;
use Illuminate\Database\Seeder;

/**
 * Creates the 2026-2027 school year with its class-day calendar and makes it the
 * active year. Sections are teacher-made now (advisers create their own
 * classes), so none are seeded.
 */
class SecondYearSeeder extends Seeder
{
    public function run(): void
    {
        $sy = SchoolYear::updateOrCreate(
            ['name' => '2026-2027'],
            ['start_date' => '2026-06-15', 'end_date' => '2027-03-31', 'is_active' => true, 'status' => SchoolYear::STATUS_OPEN]
        );

        // Exactly one year may be active: stand down any other active year so
        // SchoolYear::current() resolves unambiguously to 2026-2027.
        SchoolYear::where('id', '!=', $sy->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        SchoolYear::forgetCurrent();

        app(SchoolCalendarService::class)->generate($sy);
    }
}
