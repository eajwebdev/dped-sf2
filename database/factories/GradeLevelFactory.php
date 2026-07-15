<?php

namespace Database\Factories;

use App\Models\GradeLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradeLevel>
 */
class GradeLevelFactory extends Factory
{
    protected $model = GradeLevel::class;

    public function definition(): array
    {
        $order = fake()->unique()->numberBetween(1, 12);

        return [
            'name' => "Grade {$order}",
            'code' => "G{$order}",
            'level_order' => $order,
            'is_graduating' => false,
        ];
    }
}
