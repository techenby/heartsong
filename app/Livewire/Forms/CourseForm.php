<?php

namespace App\Livewire\Forms;

use App\Enums\Color;
use App\Enums\Day;
use App\Enums\Grade;
use App\Enums\Period;
use App\Models\Course;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Form;

class CourseForm extends Form
{
    public ?Course $course = null;

    public string $grade = '';

    public string $color = '';

    public string $homeroom = '';

    /** @var array<int, array{day: string, period: string}> */
    public array $meetings = [['day' => '', 'period' => '']];

    public function setCourse(Course $course): void
    {
        $this->course = $course;
        $this->grade = $course->grade->value;
        $this->color = $course->color->value;
        $this->homeroom = $course->homeroom;
        $this->meetings = $course->meetings
            ->map(fn ($meeting) => ['day' => $meeting->day->value, 'period' => $meeting->period->value])
            ->values()
            ->all();
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

    public function store(): Course
    {
        $this->validate();

        return DB::transaction(function () {
            $course = Course::create($this->courseAttributes());
            $course->meetings()->createMany($this->meetings);

            return $course;
        });
    }

    public function update(): void
    {
        $this->validate();

        // Meetings are replaced wholesale so swapping two time slots can't trip the
        // unique (course, day, period) index partway through the save.
        DB::transaction(function () {
            $this->course->update($this->courseAttributes());
            $this->course->meetings()->delete();
            $this->course->meetings()->createMany($this->meetings);
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'grade' => ['required', Rule::enum(Grade::class)],
            'color' => ['required', Rule::enum(Color::class)],
            'homeroom' => ['required', 'string', 'max:50'],
            'meetings' => ['required', 'array', 'min:1'],
            'meetings.*.day' => ['required', Rule::enum(Day::class)],
            'meetings.*.period' => ['required', Rule::enum(Period::class), $this->uniqueMeetingRule()],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'meetings.*.day.required' => 'Choose a day.',
            'meetings.*.period.required' => 'Choose a period.',
        ];
    }

    /**
     * @return array{grade: string, color: string, homeroom: string}
     */
    protected function courseAttributes(): array
    {
        return $this->only(['grade', 'color', 'homeroom']);
    }

    protected function uniqueMeetingRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $index = (int) explode('.', $attribute)[1];
            $meeting = $this->meetings[$index];

            $firstMatch = collect($this->meetings)->search(
                fn (array $other) => $other['day'] === $meeting['day'] && $other['period'] === $meeting['period'],
            );

            if ($firstMatch !== $index) {
                $fail('This time is already listed.');
            }
        };
    }
}
