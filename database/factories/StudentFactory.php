<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $gender = fake()->randomElement(['Male', 'Female']);

        return [
            'lrn' => fake()->unique()->numerify('############'), // 12 digits
            'first_name' => fake()->firstName($gender === 'Male' ? 'male' : 'female'),
            'middle_name' => fake()->lastName(),
            'last_name' => fake()->lastName(),
            'suffix' => fake()->optional(0.05)->randomElement(['Jr.', 'III']),
            'gender' => $gender,
            'birthdate' => fake()->dateTimeBetween('-18 years', '-12 years')->format('Y-m-d'),
            'address' => fake()->address(),
            'guardian_name' => fake()->name(),
            'guardian_contact' => fake()->numerify('09#########'),
            'status' => 'active',
            'qr_token' => (string) Str::uuid(),
        ];
    }
}
