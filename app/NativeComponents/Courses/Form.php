<?php

namespace App\NativeComponents\Courses;

use App\Actions\SaveCourse;
use App\Enums\Color;
use App\Models\Course;
use Illuminate\Validation\ValidationException;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;

class Form extends NativeComponent
{
    public ?Course $course = null;

    public string $grade = '';

    public string $color = '';

    public string $homeroom = '';

    /** @var array<int, array{day: string, period: string}> */
    public array $meetings = [['day' => '', 'period' => '']];

    /** @var array<string, array<int, string>> Validation messages keyed by field, e.g. "meetings.0.day". */
    public array $errors = [];

    public function mount(?Course $course = null): void
    {
        if (! $course) {
            return;
        }

        $this->course = $course;
        $this->grade = $course->grade->value;
        $this->color = $course->color->value;
        $this->homeroom = $course->homeroom;
        $this->meetings = $course->meetings
            ->map(fn ($meeting) => ['day' => $meeting->day->value, 'period' => $meeting->period->value])
            ->values()
            ->all();
    }

    public function navTitle(): string
    {
        return $this->course ? 'Edit class' : 'Create class';
    }

    public function setColor(string $color): void
    {
        $this->color = Color::from($color)->value;
    }

    public function addMeeting(): void
    {
        $this->meetings[] = ['day' => '', 'period' => ''];
    }

    public function duplicateMeeting(int $index): void
    {
        $this->meetings[] = $this->meetings[$index];
    }

    public function removeMeeting(int $index): void
    {
        if (count($this->meetings) === 1) {
            return;
        }

        unset($this->meetings[$index]);
        $this->meetings = array_values($this->meetings);
    }

    public function save(SaveCourse $saveCourse): void
    {
        try {
            $course = $saveCourse->handle($this->course, [
                'grade' => $this->grade,
                'color' => $this->color,
                'homeroom' => $this->homeroom,
                'meetings' => $this->meetings,
            ]);
        } catch (ValidationException $exception) {
            $this->errors = $exception->errors();

            return;
        }

        $this->errors = [];

        if ($this->course) {
            $this->back();

            return;
        }

        $this->replace('/courses/'.$course->id);
    }

    public function error(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    public function render(): Element
    {
        return $this->view('courses.form');
    }
}
