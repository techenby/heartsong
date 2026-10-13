<?php

use App\Models\Course;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Class')] class extends Component {
    public Course $course;

    /**
     * @return Collection<int, Student>
     */
    #[Computed]
    public function students(): Collection
    {
        return $this->course->students()->orderBy('last_name')->orderBy('first_name')->get();
    }

    #[On('roster-imported')]
    public function refreshRoster(): void
    {
        unset($this->students);
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-4">
        <x-color-dot :color="$course->color" size="xl" />

        <div>
            <flux:heading size="xl" level="1">{{ $course->grade->value }} - {{ $course->homeroom }}</flux:heading>
            <flux:text size="lg">{{ $course->meets }}</flux:text>
        </div>

        <flux:button :href="route('courses.edit', $course)" icon="pencil" class="ml-auto" wire:navigate>
            {{ __('Edit') }}
        </flux:button>
    </div>

    <section class="space-y-4">
        <div class="flex items-center justify-between gap-4">
            <flux:heading size="lg" level="2">{{ __('Roster') }}</flux:heading>

            <flux:modal.trigger name="import-roster">
                <flux:button size="sm" icon="arrow-up-tray">{{ __('Import roster') }}</flux:button>
            </flux:modal.trigger>
        </div>

        <livewire:courses.import-roster :$course />

        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('First name') }}</flux:table.column>
                <flux:table.column>{{ __('Last name') }}</flux:table.column>
                <flux:table.column>{{ __('Pronouns') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->students as $student)
                    <flux:table.row :key="$student->id">
                        <flux:table.cell variant="strong">{{ $student->preferred_name ?? $student->first_name }}</flux:table.cell>
                        <flux:table.cell>{{ $student->last_name }}</flux:table.cell>
                        <flux:table.cell>{{ $student->pronouns }}</flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="3">{{ __('No students yet. Import a roster to get started.') }}</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </section>
</div>
