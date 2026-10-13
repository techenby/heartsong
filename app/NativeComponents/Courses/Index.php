<?php

namespace App\NativeComponents\Courses;

use App\Concerns\ConfirmsActions;
use App\Enums\Day;
use App\Models\Course;
use App\Models\CourseMeeting;
use App\Models\Period;
use Illuminate\Database\Eloquent\Collection;
use Native\Mobile\Attributes\Computed;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;

/**
 * @property-read Collection<int, Course> $courses
 * @property-read Collection<int, Period> $periods
 */
class Index extends NativeComponent
{
    use ConfirmsActions;

    /** 0 = calendar, 1 = list */
    public int $tab = 0;

    public function navTitle(): string
    {
        return 'Classes';
    }

    /**
     * @return Collection<int, Course>
     */
    #[Computed(persist: true)]
    public function courses(): Collection
    {
        return Course::query()
            ->with('meetings')
            ->withCount('students')
            ->get()
            ->sortBy(fn (Course $course) => [$course->grade->ordinal(), $course->homeroom])
            ->values();
    }

    /**
     * @return Collection<int, Period>
     */
    #[Computed(persist: true)]
    public function periods(): Collection
    {
        return Period::query()->ordered()->get();
    }

    public function meetingAt(Day $day, Period $period): ?CourseMeeting
    {
        return $this->courses->flatMap->meetings
            ->first(fn (CourseMeeting $meeting) => $meeting->day === $day && $meeting->period_id === $period->id);
    }

    public function onResume(): void
    {
        unset($this->courses, $this->periods);
    }

    public function confirmDelete(int $courseId): void
    {
        $course = Course::findOrFail($courseId);

        $this->confirm("delete-course-{$course->id}", "Delete {$course->formatted}?", 'Its schedule, songs, roster, and testing history will be deleted too.', function () use ($course): void {
            $course->delete();
            unset($this->courses);
        });
    }

    public function render(): Element
    {
        return $this->view('courses.index');
    }
}
