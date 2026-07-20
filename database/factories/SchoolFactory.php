<?php

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    protected $model = School::class;

    public function definition(): array
    {
        return [
            'school_id' => (string) fake()->unique()->numberBetween(100000, 999999),
            'name' => fake()->company().' School',
            'education_level' => fake()->randomElement(array_keys(School::LEVELS)),
            'division' => fake()->city(),
            'region' => 'Region '.fake()->randomElement(['IV-A', 'III', 'NCR']),
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
