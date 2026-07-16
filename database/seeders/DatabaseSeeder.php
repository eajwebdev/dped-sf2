<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SchoolSeeder::class,
            AcademicStructureSeeder::class,
            // DemoDataSeeder (Maria Santos + factory learners) is intentionally
            // out of the default chain — Jade is the default teacher.
            SecondYearSeeder::class,
            JadeTeacherSeeder::class,
        ]);
    }
}
