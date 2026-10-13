<?php

use App\Actions\MoveToPosition;
use App\Models\Course;
use App\Models\Song;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Songs')] class extends Component {
    public Course $course;

    public string $name = '';

    #[Locked]
    public ?int $editingSongId = null;

    public string $editingName = '';

    /**
     * @return Collection<int, Song>
     */
    #[Computed]
    public function songs(): Collection
    {
        return $this->course->songs()->withCount('attempts')->get();
    }

    public function addSong(): void
    {
        $this->validate(['name' => ['required', 'string', 'max:255']]);

        $this->course->songs()->create([
            'name' => trim($this->name),
            'position' => ($this->course->songs()->max('position') ?? -1) + 1,
        ]);

        $this->reset('name');
        unset($this->songs);
    }

    public function editSong(int $songId): void
    {
        $song = $this->course->songs()->findOrFail($songId);

        $this->editingSongId = $song->id;
        $this->editingName = $song->name;
        $this->resetValidation();

        Flux::modal('rename-song')->show();
    }

    public function renameSong(): void
    {
        $this->validate(['editingName' => ['required', 'string', 'max:255']]);

        $this->course->songs()->findOrFail($this->editingSongId)->update(['name' => trim($this->editingName)]);

        unset($this->songs);
        Flux::modal('rename-song')->close();
    }

    public function deleteSong(int $songId): void
    {
        $this->course->songs()->findOrFail($songId)->delete();

        unset($this->songs);
    }

    public function moveSong(int $songId, int $position, MoveToPosition $moveToPosition): void
    {
        $moveToPosition->handle($this->course->songs()->get(), $songId, $position);

        unset($this->songs);
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-4">
        <x-color-dot :color="$course->color" size="xl" />

        <div>
            <flux:heading size="xl" level="1">{{ __('Songs') }}</flux:heading>
            <flux:text size="lg">{{ $course->grade->value }} - {{ $course->homeroom }}</flux:text>
        </div>

        <div class="ml-auto flex gap-2">
            <flux:button :href="route('courses.show', $course)" icon="arrow-left" variant="ghost" wire:navigate>{{ __('Back to class') }}</flux:button>
            <flux:button :href="route('courses.testing', $course)" icon="play" variant="primary" wire:navigate>{{ __('Start testing') }}</flux:button>
        </div>
    </div>

    <flux:text>{{ __('Students work through these songs in order. Drag to reorder.') }}</flux:text>

    <form wire:submit="addSong" class="flex items-start gap-2">
        <flux:input wire:model="name" :placeholder="__('Song name, e.g. Hot Cross Buns')" :aria-label="__('Song name')" class="flex-1" />
        <flux:button type="submit" icon="plus">{{ __('Add song') }}</flux:button>
    </form>

    @if ($this->songs->isEmpty())
        <flux:text>{{ __('No songs yet. Add the first song students will work on.') }}</flux:text>
    @else
        <ol wire:sort="moveSong" class="divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-white/10 dark:border-white/10">
            @foreach ($this->songs as $index => $song)
                <li wire:key="song-{{ $song->id }}" wire:sort:item="{{ $song->id }}" class="flex items-center gap-3 bg-white px-3 py-2 dark:bg-zinc-800">
                    <flux:icon.bars-2 wire:sort:handle variant="micro" class="cursor-grab text-zinc-400" />
                    <flux:text class="w-6 tabular-nums">{{ $index + 1 }}.</flux:text>
                    <flux:heading class="flex-1">{{ $song->name }}</flux:heading>

                    <flux:dropdown align="end">
                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" :aria-label="__('Actions for :song', ['song' => $song->name])" />

                        <flux:menu>
                            <flux:menu.item wire:click="editSong({{ $song->id }})" icon="pencil">{{ __('Rename') }}</flux:menu.item>
                            <flux:menu.item
                                wire:click="deleteSong({{ $song->id }})"
                                wire:confirm="{{ $song->attempts_count
                                    ? trans_choice('Delete :song and its :count recorded attempt?|Delete :song and its :count recorded attempts?', $song->attempts_count, ['song' => $song->name])
                                    : __('Delete :song?', ['song' => $song->name]) }}"
                                icon="trash"
                                variant="danger"
                            >{{ __('Delete') }}</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </li>
            @endforeach
        </ol>
    @endif

    <flux:modal name="rename-song" class="w-full max-w-md">
        <form wire:submit="renameSong" class="space-y-6">
            <flux:heading size="lg">{{ __('Rename song') }}</flux:heading>

            <flux:input wire:model="editingName" :label="__('Name')" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
