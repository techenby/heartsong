<?php

namespace App\NativeComponents\Courses;

use App\Enums\Grade;
use App\Models\Course;
use App\Models\CourseMeeting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Native\Mobile\Attributes\Computed;
use Native\Mobile\Attributes\On;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Facades\Dialog;

/**
 * @property-read Collection<int, Course> $courses
 * @property-read SupportCollection<string, CourseMeeting> $slots
 */
class Index extends NativeComponent
{
    /** 0 = calendar, 1 = list */
    public int $tab = 0;

    public function navTitle(): string
    {
        return 'Classes';
    }

    /**
     * @return Collection<int, Course>
     */
    #[Computed]
    public function courses(): Collection
    {
        return Course::query()
            ->with('meetings')
            ->withCount('students')
            ->get()
            ->sortBy([
                fn (Course $a, Course $b) => array_search($a->grade, Grade::cases()) <=> array_search($b->grade, Grade::cases()),
                fn (Course $a, Course $b) => $a->homeroom <=> $b->homeroom,
            ])
            ->values();
    }

    /**
     * Meetings keyed by "Day|Period" for the calendar grid.
     *
     * @return SupportCollection<string, CourseMeeting>
     */
    #[Computed]
    public function slots(): SupportCollection
    {
        return $this->courses->flatMap->meetings
            ->keyBy(fn (CourseMeeting $meeting) => $meeting->day->value.'|'.$meeting->period->value);
    }

    public function onResume(): void
    {
        unset($this->courses, $this->slots);
    }

    public function open(int $courseId): void
    {
        $this->navigate('/courses/'.$courseId);
    }

    public function confirmDelete(int $courseId): void
    {
        $course = Course::findOrFail($courseId);

        Dialog::alert("Delete {$course->formatted}?", 'Its schedule, songs, roster, and testing history will be deleted too.', [
            ['label' => 'Cancel', 'style' => 'cancel'],
            ['label' => 'Delete', 'style' => 'destructive'],
        ])->id("delete-course-{$course->id}");
    }

    #[On(ButtonPressed::class)]
    public function onAlertButton(int $index, string $label, ?string $id = null): void
    {
        if ($label !== 'Delete' || ! str_starts_with((string) $id, 'delete-course-')) {
            return;
        }

        Course::find((int) str($id)->after('delete-course-')->toString())?->delete();

        unset($this->courses, $this->slots);
    }

    public function render(): Element
    {
        return $this->view('courses.index');
    }
}
