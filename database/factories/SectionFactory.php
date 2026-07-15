<?php

namespace Database\Factories;

use App\Models\GradeLevel;
use App\Models\SchoolYear;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition(): array
    {
        return [
            'school_year_id' => SchoolYear::factory(),
            'grade_level_id' => GradeLevel::factory(),
            'name' => fake()->unique()->randomElement(['Rizal', 'Bonifacio', 'Mabini', 'Newton', 'Einstein', 'Darwin', 'Sampaguita', 'Ilang-Ilang']),
            'room' => 'Room '.fake()->numberBetween(100, 400),
            'capacity' => 45,
        ];
    }
}
