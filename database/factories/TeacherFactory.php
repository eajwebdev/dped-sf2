<?php

namespace Database\Factories;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Teacher>
 */
class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        $gender = fake()->randomElement(['Male', 'Female']);

        return [
            'employee_no' => fake()->unique()->numerify('T-#####'),
            'first_name' => fake()->firstName($gender === 'Male' ? 'male' : 'female'),
            'middle_name' => fake()->lastName(),
            'last_name' => fake()->lastName(),
            'gender' => $gender,
            'email' => fake()->unique()->safeEmail(),
            'contact' => fake()->numerify('09#########'),
            'is_active' => true,
        ];
    }
}
