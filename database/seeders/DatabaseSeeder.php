<?php

namespace Database\Seeders;

use App\Enums\Color;
use App\Enums\Day;
use App\Enums\Grade;
use App\Enums\Period;
use App\Models\Course;
use App\Models\CourseMeeting;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $schedule = [
            [Grade::Kindergarten, Color::Yellow, 'A101', Period::First, [Day::Monday, Day::Wednesday]],
            [Grade::First, Color::Orange, 'A104', Period::Second, [Day::Monday, Day::Wednesday]],
            [Grade::Second, Color::Green, 'B201', Period::Third, [Day::Monday, Day::Wednesday]],
            [Grade::Third, Color::Blue, 'B204', Period::First, [Day::Tuesday, Day::Thursday]],
            [Grade::Fourth, Color::Purple, 'C301', Period::Second, [Day::Tuesday, Day::Thursday]],
            [Grade::Fifth, Color::Red, 'C305', Period::Third, [Day::Tuesday, Day::Thursday, Day::Friday]],
        ];

        foreach ($schedule as [$grade, $color, $homeroom, $period, $days]) {
            Course::factory()
                ->has(CourseMeeting::factory()
                    ->count(count($days))
                    ->state(new Sequence(...array_map(fn (Day $day) => ['day' => $day, 'period' => $period], $days))), 'meetings')
                ->has(Student::factory()->count(fake()->numberBetween(18, 24)))
                ->create(['grade' => $grade, 'color' => $color, 'homeroom' => $homeroom]);
        }
    }
}
