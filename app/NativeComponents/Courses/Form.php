<?php

namespace App\NativeComponents\Courses;

use App\Actions\SaveCourse;
use App\Concerns\HasValidationErrors;
use App\Models\Course;
use App\Models\CourseMeeting;
use App\Models\Period;
use Illuminate\Database\Eloquent\Collection;
use Native\Mobile\Attributes\Computed;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;

/**
 * @property-read Collection<int, Period> $periods
 */
class Form extends NativeComponent
{
    use HasValidationErrors;

    public ?Course $course = null;

    public string $grade = '';

    public string $color = '';

    public string $homeroom = '';

    /** @var array<int, array{day: string, period_id: int|null}> */
    public array $meetings = [['day' => '', 'period_id' => null]];

    public function mount(?Course $course = null): void
    {
        if (! $course) {
            return;
        }

        $this->course = $course;
        $this->grade = $course->grade->value;
        $this->color = $course->color->value;
        $this->homeroom = $course->homeroom;
        $this->meetings = $course->meetingsInWeekOrder()
            ->map(fn (CourseMeeting $meeting) => ['day' => $meeting->day->value, 'period_id' => $meeting->period_id])
            ->all();
    }

    /**
     * @return Collection<int, Period>
     */
    #[Computed]
    public function periods(): Collection
    {
        return Period::query()->ordered()->get();
    }

    public function periodName(?int $periodId): string
    {
        return $this->periods->firstWhere('id', $periodId)->name ?? '';
    }

    public function navTitle(): string
    {
        return $this->course ? 'Edit class' : 'Create class';
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    /**
     * Meeting rows can't use native:model: `meetings.{{ $index }}.day` compiles into a PHP string
     * literal, so the row index is never interpolated. Periods also need mapping from name to id.
     */
    public function setMeeting(int $index, string $field, string $value): void
    {
        if (! isset($this->meetings[$index])) {
            return;
        }

        match ($field) {
            'day' => $this->meetings[$index]['day'] = $value,
            // The select shows period names; meetings store the period's id.
            'period' => $this->meetings[$index]['period_id'] = $this->periods->firstWhere('name', $value)?->id,
            default => null,
        };
    }

    public function addMeeting(): void
    {
        $this->meetings[] = ['day' => '', 'period_id' => null];
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
        $course = $this->recordingErrors(fn () => $saveCourse->handle($this->course, [
            'grade' => $this->grade,
            'color' => $this->color,
            'homeroom' => $this->homeroom,
            'meetings' => $this->meetings,
        ]));

        if (! $course) {
            return;
        }

        if ($this->course) {
            $this->back();

            return;
        }

        $this->replace('/courses/'.$course->id);
    }

    public function render(): Element
    {
        return $this->view('courses.form');
    }
}
