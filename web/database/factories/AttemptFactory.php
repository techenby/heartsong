<?php

namespace Database\Factories;

use App\Models\Attempt;
use App\Models\Song;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attempt>
 */
class AttemptFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'song_id' => Song::factory(),
            'attempted_on' => today(),
            'passed' => fake()->boolean(),
        ];
    }
}
