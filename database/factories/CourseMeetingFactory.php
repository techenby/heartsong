<?php

namespace Database\Factories;

use App\Enum\Day;
use App\Enum\Period;
use App\Models\Course;
use App\Models\CourseMeeting;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseMeetingFactory extends Factory
{
    protected $model = CourseMeeting::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'day' => fake()->randomElement(Day::class),
            'period' => fake()->randomElement(Period::class),
        ];
    }
}
