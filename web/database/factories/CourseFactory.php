<?php

namespace Database\Factories;

use App\Enums\Color;
use App\Enums\Grade;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'grade' => fake()->randomElement(Grade::cases()),
            'color' => fake()->randomElement(Color::cases()),
            'homeroom' => fake()->bothify('?###'),
        ];
    }
}
