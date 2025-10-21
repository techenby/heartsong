<?php

namespace Database\Factories;

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
            'day' => fake()->dayOfWeek(),
            'start' => now(),
            'end' => now(),
        ];
    }
}
