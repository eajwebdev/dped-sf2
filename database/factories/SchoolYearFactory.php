<?php

namespace Database\Factories;

use App\Models\SchoolYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolYear>
 */
class SchoolYearFactory extends Factory
{
    protected $model = SchoolYear::class;

    public function definition(): array
    {
        // Unique per test run — the small 2020–2030 range collided on the
        // school_years.name unique index when a test made several years.
        $startYear = 2000 + fake()->unique()->numberBetween(1, 900);

        return [
            'name' => $startYear.'-'.($startYear + 1),
            'start_date' => "{$startYear}-06-01",
            'end_date' => ($startYear + 1).'-03-31',
            'is_active' => false,
            'status' => SchoolYear::STATUS_OPEN,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['is_active' => true]);
    }
}
