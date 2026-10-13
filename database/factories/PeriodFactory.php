<?php

namespace Database\Factories;

use App\Models\Period;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Period>
 */
class PeriodFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hour = fake()->numberBetween(8, 14);

        return [
            'name' => 'Period '.fake()->unique()->numberBetween(100, 999),
            'starts_at' => sprintf('%02d:00', $hour),
            'ends_at' => sprintf('%02d:45', $hour),
            'position' => 99,
        ];
    }
}
