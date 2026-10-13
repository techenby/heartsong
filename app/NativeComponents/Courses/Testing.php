<?php

namespace App\NativeComponents\Courses;

use App\Actions\MoveToPosition;
use App\Models\Course;
use App\Models\Song;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Native\Mobile\Attributes\Computed;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\Layouts\Builders\NavAction;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Facades\Dialog;

/**
 * @property-read Collection<int, Song> $songs
 * @property-read Collection<int, Student> $students
 * @property-read Student|null $student
 * @property-read Song|null $song
 */
class Testing extends NativeComponent
{
    public const string CURRENT_SONG = 'Their current song';

    public Course $course;

    /** Set when the teacher picks someone out of order; otherwise the next untested student is up. */
    public ?int $selectedStudentId = null;

    /** A song label from songOptions(), or CURRENT_SONG. */
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

    /**
     * @return Collection<int, Song>
     */
    #[Computed]
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
        return $this->course->students()->inTestingOrder()->with('attempts')->get();
    }

    #[Computed]
    public function student(): ?Student
    {
        return $this->selectedStudentId
            ? $this->students->firstWhere('id', $this->selectedStudentId)
            : $this->students->first(fn (Student $student) => ! $student->testedToday());
    }

    #[Computed]
    public function song(): ?Song
    {
        if ($this->songChoice !== self::CURRENT_SONG) {
            return $this->songs->get(array_search($this->songChoice, $this->songOptions(), true) - 1);
        }

        return $this->student?->currentSong($this->songs);
    }

    /**
     * Select labels are numbered so two songs with the same name stay distinguishable.
     *
     * @return array<int, string>
     */
    public function songOptions(): array
    {
        return [self::CURRENT_SONG, ...$this->songs->values()->map(fn (Song $song, int $index) => ($index + 1).'. '.$song->name)->all()];
    }

    /**
     * @return array<int, NavAction>
     */
    public function studentMenu(int $studentId): array
    {
        return [
            NavAction::make("up-{$studentId}")->icon('arrow_upward')->label('Move up')->press("moveStudent({$studentId}, -1)"),
            NavAction::make("down-{$studentId}")->icon('arrow_downward')->label('Move down')->press("moveStudent({$studentId}, 1)"),
        ];
    }

    public function selectStudent(int $studentId): void
    {
        $this->selectedStudentId = $this->course->students()->findOrFail($studentId)->id;
        $this->songChoice = self::CURRENT_SONG;
        $this->notes = '';
        unset($this->student, $this->song);
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

        $this->selectedStudentId = null;
        $this->songChoice = self::CURRENT_SONG;
        $this->notes = '';
        unset($this->students, $this->student, $this->song);
    }

    public function moveStudent(int $studentId, int $offset): void
    {
        $students = $this->course->students()->inTestingOrder()->get();
        $index = $students->search(fn (Student $student) => $student->id === $studentId);

        app(MoveToPosition::class)->handle($students, $studentId, max(0, (int) $index + $offset), 'testing_position');

        unset($this->students, $this->student, $this->song);
    }

    public function render(): Element
    {
        return $this->view('courses.testing');
    }
}
