<?php

use App\Models\Course;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Class')] class extends Component {
    public Course $course;

    public function mount(Course $course): void
    {
        $this->course = $course->load(['meetings', 'students' => fn ($query) => $query->orderBy('last_name')->orderBy('first_name')]);
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
        <flux:heading size="lg" level="2">{{ __('Roster') }}</flux:heading>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('First name') }}</flux:table.column>
                <flux:table.column>{{ __('Last name') }}</flux:table.column>
                <flux:table.column>{{ __('Pronouns') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($course->students as $student)
                    <flux:table.row :key="$student->id">
                        <flux:table.cell variant="strong">{{ $student->preferred_name ?? $student->first_name }}</flux:table.cell>
                        <flux:table.cell>{{ $student->last_name }}</flux:table.cell>
                        <flux:table.cell>{{ $student->pronouns }}</flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="3">{{ __('No students in this class yet.') }}</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </section>
</div>
