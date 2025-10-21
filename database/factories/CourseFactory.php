<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public array $grades = [
        'Kindergarten',
        '1st Grade',
        '2nd Grade',
        '3rd Grade',
        '4th Grade',
        '5th Grade',
        '6th Grade',
        '7th Grade',
        '8th Grade',
    ];

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement($this->grades),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
