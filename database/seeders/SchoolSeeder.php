<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    /**
     * Seed the participating public/private high schools. Every school here runs
     * both Junior and Senior High, so all are tagged JHS + SHS. Mabinay schools
     * take the shared default location; schools in other divisions carry their
     * own. Idempotent: keyed on the DepEd School ID, safe to re-run.
     */
    public function run(): void
    {
        // Default location — the Mabinay, Negros Oriental cluster.
        $default = [
            'division' => 'Negros Oriental',
            'region' => 'Region VII',
            'address' => 'Mabinay, Negros Oriental',
        ];

        $schools = [
            // Mabinay, Negros Oriental (Region VII)
            ['school_id' => '303211', 'name' => 'Bagtic National High School'],
            ['school_id' => '303234', 'name' => 'Inapoy High School'],
            ['school_id' => '303244', 'name' => 'Mabinay National High School'],
            ['school_id' => '303245', 'name' => 'Barras Annex National High School'],
            ['school_id' => '303246', 'name' => 'Dahile National High School'],
            ['school_id' => '303247', 'name' => 'Paniabonan High School'],
            ['school_id' => '303248', 'name' => 'Tara Provincial Community High School'],
            ['school_id' => '303260', 'name' => 'Pantao National High School'],
            ['school_id' => '312923', 'name' => 'Canggohob High School'],
            ['school_id' => '312924', 'name' => 'Campanun-an Senior High School'],
            ['school_id' => '312925', 'name' => 'Cansal-ing Provincial Community High School'],
            ['school_id' => '312951', 'name' => 'Mabinay Science High School'],
            ['school_id' => '312959', 'name' => 'Mayaposi Community High School'],
            ['school_id' => '312967', 'name' => 'Mabinay National High School - Manlingay Annex / Manlingay High School'],

            // Sipalay City & Hinoba-an (Negros Island Region)
            ['school_id' => '302638', 'name' => 'Mariano Gemora National High School',
                'division' => 'SDO Sipalay City', 'region' => 'Negros Island Region',
                'address' => 'Purok Kakahuyan, Brgy Cartagena, Sipalay City'],
            ['school_id' => '404061', 'name' => "ST. MICHAEL'S ACADEMY OF HINOBA-AN, INC.",
                'division' => 'Negros Occidental District of Hinoba-an', 'region' => 'Negros Island Region',
                'address' => 'Purok 1, Barangay 1, Hinoba-an, Philippines'],
            ['school_id' => '302632', 'name' => 'Gil Montilla National High School',
                'division' => 'SDO Sipalay City', 'region' => 'Negros Island Region',
                'address' => 'Brgy. Gil Montilla, Sipalay City, Negros Island Region'],
        ];

        foreach ($schools as $school) {
            School::updateOrCreate(
                ['school_id' => $school['school_id']],
                [
                    'name' => $school['name'],
                    // Every school here offers both Junior and Senior High.
                    'education_level' => School::LEVEL_JHS_SHS,
                    'division' => $school['division'] ?? $default['division'],
                    'region' => $school['region'] ?? $default['region'],
                    'address' => $school['address'] ?? $default['address'],
                    'is_active' => true,
                ],
            );
        }
    }
}
