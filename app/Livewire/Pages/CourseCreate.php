<?php

namespace App\Livewire\Pages;

use App\Enum\Color;
use App\Enum\Day;
use App\Enum\Grade;
use App\Enum\Period;
use App\Models\Course;
use Flux\Flux;
use Livewire\Component;

class CourseCreate extends Component
{
    public string $grade;

    public Period $period;

    public string $color;

    public string $homeroom;

    public array $meetings = [['day' => '', 'period' => '']];

    public function add(): void
    {
        $this->meetings[] = ['day' => '', 'period' => ''];
    }

    public function duplicate($index): void
    {
        $this->meetings[] = $this->meetings[$index];
    }

    public function remove($index): void
    {
        if (count($this->meetings) === 1) {
            Flux::toast('Courses must have at least one time.');

            return;
        }

        unset($this->meetings[$index]);
        $this->meetings = array_values($this->meetings);
    }

    public function save(): void
    {
        $course = Course::create([
            'grade' => Grade::{$this->grade},
            'color' => Color::{$this->color},
            'homeroom' => $this->homeroom,
        ]);

        $course->meetings()->createMany(array_map(fn ($meeting) => [
            'day' => Day::{$meeting['day']},
            'period' => Period::{$meeting['period']},
        ], $this->meetings));

        $this->redirectRoute('courses');
    }
}
