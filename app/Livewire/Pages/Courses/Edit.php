<?php

namespace App\Livewire\Pages\Courses;

use App\Enum\Color;
use App\Enum\Day;
use App\Enum\Grade;
use App\Enum\Period;
use App\Models\Course;
use Flux\Flux;
use Livewire\Component;

class Edit extends Component
{
    public Course $course;

    public string $grade;

    public Period $period;

    public string $color;

    public string $homeroom;

    public array $meetings = [['day' => '', 'period' => '']];

    public function mount(): void
    {
        $this->grade = $this->course->grade->name;
        $this->color = $this->course->color->name;
        $this->homeroom = $this->course->homeroom;

        $this->meetings = $this->course->meetings->map(fn ($meeting) => ['id' => $meeting->id, 'day' => $meeting->day->name, 'period' => $meeting->period->name])->toArray();
    }

    public function add(): void
    {
        $this->meetings[] = ['day' => '', 'period' => ''];
    }

    public function duplicate($index): void
    {
        unset($this->meetings[$index]['id']);
        $this->meetings[] = $this->meetings[$index];
    }

    public function remove($index): void
    {
        if (count($this->meetings) === 1) {
            Flux::toast('Courses must have at least one time.');

            return;
        }

        if (isset($this->meetings[$index]['id'])) {
            $this->course->meetings->firstWhere('id', $this->meetings[$index]['id'])->delete();
        }

        unset($this->meetings[$index]);
        $this->meetings = array_values($this->meetings);
    }

    public function save(): void
    {
        $this->course->update([
            'grade' => Grade::{$this->grade},
            'color' => Color::{$this->color},
            'homeroom' => $this->homeroom,
        ]);

        foreach ($this->meetings as $meeting) {
            $data = [
                'day' => Day::{$meeting['day']},
                'period' => Period::{$meeting['period']},
            ];

            if (isset($meeting['id'])) {
                $this->course->meetings->firstWhere('id', $meeting['id'])->update($data);
            } else {
                $this->course->meetings()->create($data);
            }
        }

        $this->redirectRoute('courses.show', $this->course);
    }
}
