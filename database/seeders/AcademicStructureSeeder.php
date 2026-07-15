<?php

namespace Database\Seeders;

use App\Models\AttendanceSetting;
use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Services\SchoolCalendarService;
use Illuminate\Database\Seeder;

class AcademicStructureSeeder extends Seeder
{
    public function run(): void
    {
        // Global default attendance settings.
        AttendanceSetting::firstOrCreate(['school_year_id' => null], [
            'edit_lock_days' => 7,
            'autosave_seconds' => 15,
            'block_future_dates' => true,
            'allow_holiday_override' => false,
            'count_late_as_present' => true,
        ]);

        // Grade levels (Junior + Senior High School). level_order drives promotion.
        $grades = [
            ['name' => 'Grade 7', 'code' => 'G7', 'level_order' => 7, 'is_graduating' => false],
            ['name' => 'Grade 8', 'code' => 'G8', 'level_order' => 8, 'is_graduating' => false],
            ['name' => 'Grade 9', 'code' => 'G9', 'level_order' => 9, 'is_graduating' => false],
            ['name' => 'Grade 10', 'code' => 'G10', 'level_order' => 10, 'is_graduating' => false],
            ['name' => 'Grade 11', 'code' => 'G11', 'level_order' => 11, 'is_graduating' => false],
            ['name' => 'Grade 12', 'code' => 'G12', 'level_order' => 12, 'is_graduating' => true],
        ];
        foreach ($grades as $g) {
            GradeLevel::updateOrCreate(['code' => $g['code']], $g);
        }

        // A small set of common subjects tied to Grade 7 (extendable via CRUD).
        $g7 = GradeLevel::where('code', 'G7')->first();
        $subjects = [
            ['name' => 'Filipino', 'code' => 'FIL7'],
            ['name' => 'English', 'code' => 'ENG7'],
            ['name' => 'Mathematics', 'code' => 'MATH7'],
            ['name' => 'Science', 'code' => 'SCI7'],
            ['name' => 'Araling Panlipunan', 'code' => 'AP7'],
            ['name' => 'MAPEH', 'code' => 'MAPEH7'],
            ['name' => 'Edukasyon sa Pagpapakatao', 'code' => 'ESP7'],
            ['name' => 'TLE', 'code' => 'TLE7'],
        ];
        foreach ($subjects as $s) {
            Subject::updateOrCreate(['code' => $s['code']], $s + [
                'grade_level_id' => $g7?->id,
                'is_active' => true,
            ]);
        }

        // Active school year + generated day-by-day calendar.
        $sy = SchoolYear::updateOrCreate(
            ['name' => '2025-2026'],
            [
                'start_date' => '2025-06-16',
                'end_date' => '2026-03-31',
                'is_active' => true,
                'status' => SchoolYear::STATUS_OPEN,
            ]
        );

        app(SchoolCalendarService::class)->generate($sy);
    }
}
