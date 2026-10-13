<?php

use App\Actions\MoveToPosition;
use App\Models\Course;
use App\Models\Song;
use App\Models\Student;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Testing')] class extends Component {
    public Course $course;

    /** Set when the teacher picks someone out of order; otherwise the next untested student is up. */
    #[Locked]
    public ?int $selectedStudentId = null;

    /** Empty means the student's current song. */
    public string $songId = '';

    public string $notes = '';

    /**
     * @return Collection<int, Song>
     */
    #[Computed]
    public function songs(): Collection
    {
        return $this->course->songs;
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
        if ($this->songId !== '') {
            return $this->songs->firstWhere('id', (int) $this->songId);
        }

        return $this->student?->currentSong($this->songs);
    }

    public function selectStudent(int $studentId): void
    {
        $this->selectedStudentId = $this->course->students()->findOrFail($studentId)->id;
        $this->reset('songId', 'notes');
        $this->resetValidation();
        unset($this->student, $this->song);
    }

    public function record(bool $passed): void
    {
        $this->validate([
            'songId' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        abort_unless($this->student && $this->song, 404);

        $this->student->attempts()->create([
            'song_id' => $this->song->id,
            'attempted_on' => today(),
            'passed' => $passed,
            'notes' => trim($this->notes) ?: null,
        ]);

        Flux::toast(
            variant: $passed ? 'success' : 'warning',
            text: __(':name: :result on :song', [
                'name' => $this->student->display_name,
                'result' => $passed ? __('passed') : __('not yet'),
                'song' => $this->song->name,
            ]),
        );

        $this->reset('selectedStudentId', 'songId', 'notes');
        unset($this->students, $this->student, $this->song);
    }

    public function moveStudent(int $studentId, int $position, MoveToPosition $moveToPosition): void
    {
        $moveToPosition->handle($this->course->students()->inTestingOrder()->get(), $studentId, $position, 'testing_position');

        unset($this->students, $this->student, $this->song);
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-4">
        <x-color-dot :color="$course->color" size="xl" />

        <div>
            <flux:heading size="xl" level="1">{{ __('Testing') }}</flux:heading>
            <flux:text size="lg">{{ $course->grade->value }} - {{ $course->homeroom }} · {{ today()->toFormattedDayDateString() }}</flux:text>
        </div>

        <div class="ml-auto flex gap-2">
            <flux:button :href="route('courses.show', $course)" icon="arrow-left" variant="ghost" wire:navigate>{{ __('Back to class') }}</flux:button>
            <flux:button :href="route('courses.songs', $course)" icon="musical-note" wire:navigate>{{ __('Songs') }}</flux:button>
        </div>
    </div>

    @if ($this->songs->isEmpty())
        <flux:callout icon="musical-note" :heading="__('Add songs to start testing')">
            <x-slot name="actions">
                <flux:button :href="route('courses.songs', $course)" wire:navigate>{{ __('Add songs') }}</flux:button>
            </x-slot>
        </flux:callout>
    @elseif ($this->students->isEmpty())
        <flux:callout icon="users" :heading="__('Import a roster to start testing')">
            <x-slot name="actions">
                <flux:button :href="route('courses.show', $course)" wire:navigate>{{ __('Go to roster') }}</flux:button>
            </x-slot>
        </flux:callout>
    @else
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            <flux:card class="space-y-6 self-start">
                @if ($this->student)
                    <div>
                        <flux:text>{{ $selectedStudentId ? __('Testing') : __('Up next') }}</flux:text>
                        <flux:heading size="xl">{{ $this->student->display_name }}</flux:heading>
                        @if ($this->student->pronouns)
                            <flux:text>{{ $this->student->pronouns }}</flux:text>
                        @endif
                    </div>

                    <flux:select wire:model.live="songId" :label="__('Song')">
                        <flux:select.option value="">
                            {{ $this->student->currentSong($this->songs)?->name
                                ? __('Current song: :song', ['song' => $this->student->currentSong($this->songs)->name])
                                : __('Choose a song (all songs passed)') }}
                        </flux:select.option>
                        @foreach ($this->songs as $song)
                            <flux:select.option :value="$song->id">{{ $song->name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    @if ($this->song)
                        <flux:text>
                            {{ trans_choice('{0} First try on :song|{1} 1 previous try on :song|[2,*] :count previous tries on :song', $this->student->triesOn($this->song), ['song' => $this->song->name]) }}
                        </flux:text>

                        <flux:textarea wire:model="notes" :label="__('Notes')" rows="2" :placeholder="__('Optional')" />

                        <div class="flex gap-2">
                            <flux:button wire:click="record(true)" variant="primary" icon="check" class="flex-1">{{ __('Passed') }}</flux:button>
                            <flux:button wire:click="record(false)" icon="arrow-path" class="flex-1">{{ __('Not yet') }}</flux:button>
                        </div>
                    @endif
                @else
                    <div class="space-y-2">
                        <flux:heading size="lg">{{ __('Everyone has been tested today') }}</flux:heading>
                        <flux:text>{{ __('Pick a student from the list to test them again.') }}</flux:text>
                    </div>
                @endif
            </flux:card>

            <div class="space-y-2">
                <flux:heading size="lg" level="2">{{ __('Testing order') }}</flux:heading>
                <flux:text size="sm">{{ __('Drag to change the order students go in.') }}</flux:text>

                <ol wire:sort="moveStudent" class="divide-y divide-zinc-200 rounded-lg border border-zinc-200 dark:divide-white/10 dark:border-white/10">
                    @foreach ($this->students as $student)
                        @php($todaysAttempt = $student->testedToday())
                        @php($currentSong = $student->currentSong($this->songs))

                        <li
                            wire:key="student-{{ $student->id }}"
                            wire:sort:item="{{ $student->id }}"
                            @class([
                                'flex items-center gap-3 px-3 py-2',
                                'bg-zinc-100 dark:bg-zinc-700' => $this->student?->is($student),
                                'bg-white dark:bg-zinc-800' => ! $this->student?->is($student),
                            ])
                        >
                            <flux:icon.bars-2 wire:sort:handle variant="micro" class="cursor-grab text-zinc-400" />

                            <button type="button" wire:click="selectStudent({{ $student->id }})" class="flex-1 text-start">
                                <flux:heading>{{ $student->display_name }}</flux:heading>
                                <flux:text size="sm">{{ $currentSong?->name ?? __('All songs passed') }}</flux:text>
                            </button>

                            @if ($todaysAttempt)
                                <flux:badge size="sm" :color="$todaysAttempt->passed ? 'green' : 'amber'">
                                    {{ $todaysAttempt->passed ? __('Passed') : __('Not yet') }}
                                </flux:badge>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    @endif
</div>
