<?php

namespace Database\Factories;

use App\Enum\Color;
use App\Enum\Grade;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'grade' => fake()->randomElement(Grade::class),
            'color' => fake()->randomElement(Color::class),
            'homeroom' => 'A123',
        ];
    }
}
