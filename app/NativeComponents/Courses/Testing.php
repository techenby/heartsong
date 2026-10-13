<?php

namespace App\NativeComponents\Courses;

use App\Actions\MoveToPosition;
use App\Concerns\ReordersRows;
use App\Models\Attempt;
use App\Models\Course;
use App\Models\Song;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Native\Mobile\Attributes\Computed;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\Layouts\Builders\NavAction;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Facades\Dialog;

/**
 * @property-read Collection<int, Song> $songs
 * @property-read Collection<int, Student> $students
 * @property-read SupportCollection<int, Attempt|null> $todaysAttempts
 * @property-read Student|null $student
 * @property-read Song|null $song
 */
class Testing extends NativeComponent
{
    use ReordersRows;

    public const string CURRENT_SONG = 'Their current song';

    public Course $course;

    /** Set when the teacher picks someone out of order; otherwise the next untested student is up. */
    public ?int $selectedStudentId = null;

    /** A label from songOptions(): a numbered song, or CURRENT_SONG. */
    public string $songChoice = self::CURRENT_SONG;

    public string $notes = '';

    public function mount(Course $course): void
    {
        $this->course = $course;
    }

    public function navTitle(): string
    {
        return 'Testing';
    }

    public function onResume(): void
    {
        unset($this->songs);
    }

    /**
     * @return Collection<int, Song>
     */
    #[Computed(persist: true)]
    public function songs(): Collection
    {
        return $this->course->songs()->get();
    }

    /**
     * @return Collection<int, Student>
     */
    #[Computed]
    public function students(): Collection
    {
        return $this->course->students()
            ->inTestingOrder()
            ->with(['attempts' => fn ($query) => $query->select(['id', 'student_id', 'song_id', 'passed', 'attempted_on'])])
            ->get();
    }

    /**
     * Each student's latest attempt from today, keyed by student id.
     *
     * @return SupportCollection<int, Attempt|null>
     */
    #[Computed]
    public function todaysAttempts(): SupportCollection
    {
        return $this->students->mapWithKeys(fn (Student $student) => [$student->id => $student->testedToday()]);
    }

    #[Computed]
    public function student(): ?Student
    {
        return $this->selectedStudentId
            ? $this->students->firstWhere('id', $this->selectedStudentId)
            : $this->students->first(fn (Student $student) => ! $this->todaysAttempts->get($student->id));
    }

    #[Computed]
    public function song(): ?Song
    {
        $songId = array_search($this->songChoice, $this->songOptions(), true);

        return $songId ? $this->songs->find($songId) : $this->student?->currentSong($this->songs);
    }

    /**
     * Select labels keyed by song id (0 = current song). Labels are numbered so two songs
     * with the same name stay distinguishable.
     *
     * @return array<int, string>
     */
    public function songOptions(): array
    {
        return [0 => self::CURRENT_SONG] + $this->songs->values()
            ->mapWithKeys(fn (Song $song, int $index) => [$song->id => ($index + 1).'. '.$song->name])
            ->all();
    }

    /**
     * @return array<int, NavAction>
     */
    public function studentMenu(int $studentId): array
    {
        return $this->moveMenuItems('moveStudent', $studentId);
    }

    public function selectStudent(int $studentId): void
    {
        $this->resetTurn($this->course->students()->findOrFail($studentId)->id);
    }

    public function updatedSongChoice(): void
    {
        unset($this->song);
    }

    public function record(bool $passed): void
    {
        $student = $this->student;
        $song = $this->song;

        if (! $student || ! $song) {
            return;
        }

        $student->attempts()->create([
            'song_id' => $song->id,
            'attempted_on' => today(),
            'passed' => $passed,
            'notes' => trim($this->notes) ?: null,
        ]);

        Dialog::toast("{$student->display_name}: ".($passed ? 'passed' : 'not yet')." on {$song->name}");

        unset($this->students);
        $this->resetTurn();
    }

    public function moveStudent(int $studentId, int $offset): void
    {
        app(MoveToPosition::class)->by($this->course->students()->inTestingOrder()->get(), $studentId, $offset, 'testing_position');

        unset($this->students);
        $this->resetTurn($this->selectedStudentId);
    }

    protected function resetTurn(?int $studentId = null): void
    {
        $this->selectedStudentId = $studentId;
        $this->songChoice = self::CURRENT_SONG;
        $this->notes = '';
        unset($this->todaysAttempts, $this->student, $this->song);
    }

    public function render(): Element
    {
        return $this->view('courses.testing');
    }
}
