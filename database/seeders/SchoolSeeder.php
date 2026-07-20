<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    /**
     * Seed the public high schools of Mabinay, Negros Oriental.
     * Idempotent: keyed on the DepEd School ID, safe to re-run.
     */
    public function run(): void
    {
        $schools = [
            ['school_id' => '303211', 'name' => 'Bagtic National High School'],
            ['school_id' => '303234', 'name' => 'Inapoy High School'],
            ['school_id' => '303244', 'name' => 'Mabinay National High School'],
            ['school_id' => '303245', 'name' => 'Barras Annex National High School'],
            ['school_id' => '303246', 'name' => 'Dahile National High School'],
            ['school_id' => '303247', 'name' => 'Paniabonan High School'],
            ['school_id' => '303248', 'name' => 'Tara Provincial Community High School'],
            ['school_id' => '303260', 'name' => 'Pantao National High School'],
            ['school_id' => '312923', 'name' => 'Canggohob High School'],
            ['school_id' => '312924', 'name' => 'Campanun-an Senior High School', 'education_level' => School::LEVEL_SHS],
            ['school_id' => '312925', 'name' => 'Cansal-ing Provincial Community High School'],
            ['school_id' => '312951', 'name' => 'Mabinay Science High School'],
            ['school_id' => '312959', 'name' => 'Mayaposi Community High School'],
            ['school_id' => '312967', 'name' => 'Mabinay National High School - Manlingay Annex / Manlingay High School'],
        ];

        foreach ($schools as $school) {
            School::updateOrCreate(
                ['school_id' => $school['school_id']],
                [
                    'name' => $school['name'],
                    // These are all high schools; most national high schools now
                    // run both JHS and SHS, so default to the combined level.
                    'education_level' => $school['education_level'] ?? School::LEVEL_JHS_SHS,
                    'division' => 'Negros Oriental',
                    'region' => 'Region VII',
                    'address' => 'Mabinay, Negros Oriental',
                    'is_active' => true,
                ],
            );
        }
    }
}
