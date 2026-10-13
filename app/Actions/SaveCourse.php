<?php

namespace App\Actions;

use App\Enums\Color;
use App\Enums\Day;
use App\Enums\Grade;
use App\Enums\Period;
use App\Models\Course;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaveCourse
{
    /**
     * Create or update a course and replace its meeting times.
     *
     * @param  array{grade: string, color: string, homeroom: string, meetings: array<int, array{day: string, period: string}>}  $data
     *
     * @throws ValidationException
     */
    public function handle(?Course $course, array $data): Course
    {
        $validated = Validator::make($data, $this->rules($data['meetings']), $this->messages())->validate();

        // Meetings are replaced wholesale so swapping two time slots can't trip the
        // unique (course, day, period) index partway through the save.
        return DB::transaction(function () use ($course, $validated) {
            $attributes = [
                'grade' => $validated['grade'],
                'color' => $validated['color'],
                'homeroom' => trim($validated['homeroom']),
            ];

            if ($course) {
                $course->update($attributes);
                $course->meetings()->delete();
            } else {
                $course = Course::create($attributes);
            }

            $course->meetings()->createMany(array_values($validated['meetings']));

            return $course;
        });
    }

    /**
     * @param  array<int, array{day: string, period: string}>  $meetings
     * @return array<string, mixed>
     */
    protected function rules(array $meetings): array
    {
        return [
            'grade' => ['required', Rule::enum(Grade::class)],
            'color' => ['required', Rule::enum(Color::class)],
            'homeroom' => ['required', 'string', 'max:50'],
            'meetings' => ['required', 'array', 'min:1'],
            'meetings.*.day' => ['required', Rule::enum(Day::class)],
            'meetings.*.period' => ['required', Rule::enum(Period::class), $this->uniqueMeetingRule($meetings)],
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
     * @param  array<int, array{day: string, period: string}>  $meetings
     */
    protected function uniqueMeetingRule(array $meetings): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($meetings): void {
            $index = (int) explode('.', $attribute)[1];
            $meeting = $meetings[$index];

            $firstMatch = collect($meetings)->search(
                fn (array $other) => $other['day'] === $meeting['day'] && $other['period'] === $meeting['period'],
            );

            if ($firstMatch !== $index) {
                $fail('This time is already listed.');
            }
        };
    }
}
