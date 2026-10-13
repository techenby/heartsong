<?php

namespace Database\Factories;

use App\Enums\Day;
use App\Models\Course;
use App\Models\CourseMeeting;
use App\Models\Period;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseMeeting>
 */
class CourseMeetingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'day' => fake()->randomElement(Day::cases()),
            'period_id' => Period::factory(),
        ];
    }
}
