<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Mathematics', 'Science', 'English', 'Filipino', 'Araling Panlipunan', 'MAPEH', 'ESP', 'TLE']);

        return [
            'name' => $name,
            'code' => strtoupper(substr($name, 0, 4)).fake()->unique()->numberBetween(1, 999),
            'grade_level_id' => null,
            'units' => 1,
            'is_active' => true,
        ];
    }
}
