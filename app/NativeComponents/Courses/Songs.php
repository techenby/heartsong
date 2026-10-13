<?php

namespace App\NativeComponents\Courses;

use App\Actions\MoveToPosition;
use App\Concerns\ConfirmsActions;
use App\Concerns\HasValidationErrors;
use App\Concerns\ReordersRows;
use App\Models\Course;
use App\Models\Song;
use Illuminate\Database\Eloquent\Collection;
use Native\Mobile\Attributes\Computed;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\Layouts\Builders\NavAction;
use Native\Mobile\Edge\NativeComponent;

/**
 * @property-read Collection<int, Song> $songs
 */
class Songs extends NativeComponent
{
    use ConfirmsActions, HasValidationErrors, ReordersRows;

    public Course $course;

    public string $name = '';

    /** The song being renamed; the rename sheet is open while this is set. */
    public ?int $editingSongId = null;

    public string $editingName = '';

    public function mount(Course $course): void
    {
        $this->course = $course;
    }

    public function navTitle(): string
    {
        return 'Songs';
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
        return $this->course->songs()->withCount('attempts')->get();
    }

    /**
     * @return array<int, NavAction>
     */
    public function songMenu(int $songId): array
    {
        return [
            NavAction::make("rename-{$songId}")->icon('edit')->label('Rename')->press("editSong({$songId})"),
            ...$this->moveMenuItems('moveSong', $songId),
            NavAction::divider(),
            NavAction::make("delete-{$songId}")->icon('delete')->label('Delete')->press("confirmDelete({$songId})")->destructive(),
        ];
    }

    public function addSong(): void
    {
        if (! $this->validateOrRecord(['name' => $this->name], $this->nameRules('name'), [], ['name' => 'song name'])) {
            return;
        }

        $this->course->songs()->create(['name' => trim($this->name)]);

        $this->name = '';
        unset($this->songs);
    }

    public function editSong(int $songId): void
    {
        $song = $this->course->songs()->findOrFail($songId);

        $this->editingSongId = $song->id;
        $this->editingName = $song->name;
        $this->errors = [];
    }

    public function renameSong(): void
    {
        if (! $this->validateOrRecord(['editingName' => $this->editingName], $this->nameRules('editingName'), [], ['editingName' => 'song name'])) {
            return;
        }

        $this->course->songs()->findOrFail($this->editingSongId)->update(['name' => trim($this->editingName)]);

        $this->closeRename();
        unset($this->songs);
    }

    public function closeRename(): void
    {
        $this->editingSongId = null;
        $this->errors = [];
    }

    public function moveSong(int $songId, int $offset): void
    {
        app(MoveToPosition::class)->by($this->course->songs()->get(), $songId, $offset);

        unset($this->songs);
    }

    public function confirmDelete(int $songId): void
    {
        $song = $this->course->songs()->withCount('attempts')->findOrFail($songId);

        $message = $song->attempts_count
            ? trans_choice('Its :count recorded attempt will be deleted too.|Its :count recorded attempts will be deleted too.', $song->attempts_count)
            : 'This cannot be undone.';

        $this->confirm("delete-song-{$song->id}", "Delete {$song->name}?", $message, function () use ($song): void {
            $song->delete();
            unset($this->songs);
        });
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function nameRules(string $field): array
    {
        return [$field => ['required', 'string', 'max:255']];
    }

    public function render(): Element
    {
        return $this->view('courses.songs');
    }
}
