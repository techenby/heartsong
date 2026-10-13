<?php

namespace App\NativeComponents\Courses;

use App\Actions\MoveToPosition;
use App\Models\Course;
use App\Models\Song;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use Native\Mobile\Attributes\Computed;
use Native\Mobile\Attributes\On;
use Native\Mobile\Edge\Element;
use Native\Mobile\Edge\Layouts\Builders\NavAction;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Facades\Dialog;

/**
 * @property-read Collection<int, Song> $songs
 */
class Songs extends NativeComponent
{
    public Course $course;

    public string $name = '';

    public bool $showRename = false;

    public ?int $editingSongId = null;

    public string $editingName = '';

    /** @var array<string, array<int, string>> */
    public array $errors = [];

    public function mount(Course $course): void
    {
        $this->course = $course;
    }

    public function navTitle(): string
    {
        return 'Songs';
    }

    /**
     * @return Collection<int, Song>
     */
    #[Computed]
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
            NavAction::make("up-{$songId}")->icon('arrow_upward')->label('Move up')->press("moveSong({$songId}, -1)"),
            NavAction::make("down-{$songId}")->icon('arrow_downward')->label('Move down')->press("moveSong({$songId}, 1)"),
            NavAction::divider(),
            NavAction::make("delete-{$songId}")->icon('delete')->label('Delete')->press("confirmDelete({$songId})")->destructive(),
        ];
    }

    public function addSong(): void
    {
        if (! $this->passes(['name' => $this->name])) {
            return;
        }

        $this->course->songs()->create([
            'name' => trim($this->name),
            'position' => ($this->course->songs()->max('position') ?? -1) + 1,
        ]);

        $this->name = '';
        unset($this->songs);
    }

    public function editSong(int $songId): void
    {
        $song = $this->course->songs()->findOrFail($songId);

        $this->editingSongId = $song->id;
        $this->editingName = $song->name;
        $this->errors = [];
        $this->showRename = true;
    }

    public function renameSong(): void
    {
        if (! $this->passes(['editingName' => $this->editingName])) {
            return;
        }

        $this->course->songs()->findOrFail($this->editingSongId)->update(['name' => trim($this->editingName)]);

        $this->closeRename();
        unset($this->songs);
    }

    public function closeRename(): void
    {
        $this->showRename = false;
        $this->editingSongId = null;
        $this->errors = [];
    }

    public function moveSong(int $songId, int $offset): void
    {
        $songs = $this->course->songs()->get();
        $index = $songs->search(fn (Song $song) => $song->id === $songId);

        app(MoveToPosition::class)->handle($songs, $songId, max(0, (int) $index + $offset));

        unset($this->songs);
    }

    public function confirmDelete(int $songId): void
    {
        $song = $this->course->songs()->withCount('attempts')->findOrFail($songId);

        Dialog::alert("Delete {$song->name}?", $song->attempts_count
            ? trans_choice('Its :count recorded attempt will be deleted too.|Its :count recorded attempts will be deleted too.', $song->attempts_count)
            : 'This cannot be undone.', [
                ['label' => 'Cancel', 'style' => 'cancel'],
                ['label' => 'Delete', 'style' => 'destructive'],
            ])->id("delete-song-{$song->id}");
    }

    #[On(ButtonPressed::class)]
    public function onAlertButton(int $index, string $label, ?string $id = null): void
    {
        if ($label !== 'Delete' || ! str_starts_with((string) $id, 'delete-song-')) {
            return;
        }

        $this->course->songs()->find((int) str($id)->after('delete-song-')->toString())?->delete();

        unset($this->songs);
    }

    /**
     * @param  array<string, string>  $data
     */
    protected function passes(array $data): bool
    {
        $validator = Validator::make($data, array_fill_keys(array_keys($data), ['required', 'string', 'max:255']), [], [
            'name' => 'song name',
            'editingName' => 'song name',
        ]);

        $this->errors = $validator->errors()->toArray();

        return $validator->passes();
    }

    public function render(): Element
    {
        return $this->view('courses.songs');
    }
}
