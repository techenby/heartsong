<?php

use App\Models\Attempt;
use App\Models\Course;
use App\Models\Student;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Class')] class extends Component {
    public Course $course;

    #[Locked]
    public ?int $editingStudentId = null;

    #[Locked]
    public ?int $historyStudentId = null;

    public string $preferredName = '';

    public string $pronouns = '';

    /**
     * @return Collection<int, Student>
     */
    #[Computed]
    public function students(): Collection
    {
        return $this->course->students()->orderBy('last_name')->orderBy('first_name')->get();
    }

    /**
     * @return Collection<int, Student>
     */
    #[Computed]
    public function formerStudents(): Collection
    {
        return $this->course->students()->onlyTrashed()->orderBy('last_name')->orderBy('first_name')->get();
    }

    #[Computed]
    public function editingStudent(): ?Student
    {
        return $this->editingStudentId ? $this->course->students()->findOrFail($this->editingStudentId) : null;
    }

    #[Computed]
    public function historyStudent(): ?Student
    {
        return $this->historyStudentId
            ? $this->course->students()->withTrashed()->with(['attempts' => fn ($query) => $query->with('song')->latest('attempted_on')->latest('id')])->findOrFail($this->historyStudentId)
            : null;
    }

    #[On('roster-imported')]
    public function refreshRoster(): void
    {
        unset($this->students, $this->formerStudents);
    }

    public function editStudent(int $studentId): void
    {
        $student = $this->course->students()->findOrFail($studentId);

        $this->editingStudentId = $student->id;
        $this->preferredName = $student->preferred_name ?? '';
        $this->pronouns = $student->pronouns ?? '';
        $this->resetValidation();
        unset($this->editingStudent);

        Flux::modal('edit-student')->show();
    }

    public function saveStudent(): void
    {
        $this->validate([
            'preferredName' => ['nullable', 'string', 'max:100'],
            'pronouns' => ['nullable', 'string', 'max:50'],
        ]);

        $this->editingStudent->update([
            'preferred_name' => trim($this->preferredName) ?: null,
            'pronouns' => trim($this->pronouns) ?: null,
        ]);

        unset($this->students);
        Flux::modal('edit-student')->close();
    }

    public function showHistory(int $studentId): void
    {
        $this->historyStudentId = $this->course->students()->withTrashed()->findOrFail($studentId)->id;
        unset($this->historyStudent);

        Flux::modal('student-history')->show();
    }

    public function deleteAttempt(int $attemptId): void
    {
        Attempt::query()
            ->whereBelongsTo($this->historyStudent, 'student')
            ->findOrFail($attemptId)
            ->delete();

        unset($this->historyStudent);
    }

    public function withdrawStudent(int $studentId): void
    {
        $this->course->students()->findOrFail($studentId)->delete();

        unset($this->students, $this->formerStudents);
    }

    public function restoreStudent(int $studentId): void
    {
        $this->course->students()->onlyTrashed()->findOrFail($studentId)->restore();

        unset($this->students, $this->formerStudents);
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-4">
        <x-color-dot :color="$course->color" size="xl" />

        <div>
            <flux:heading size="xl" level="1">{{ $course->grade->value }} - {{ $course->homeroom }}</flux:heading>
            <flux:text size="lg">{{ $course->meets }}</flux:text>
        </div>

        <div class="ml-auto flex gap-2">
            <flux:button :href="route('courses.edit', $course)" icon="pencil" wire:navigate>{{ __('Edit') }}</flux:button>
            <flux:button :href="route('courses.songs', $course)" icon="musical-note" wire:navigate>{{ __('Songs') }}</flux:button>
            <flux:button :href="route('courses.testing', $course)" icon="play" variant="primary" wire:navigate>{{ __('Start testing') }}</flux:button>
        </div>
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
                <flux:table.column><span class="sr-only">{{ __('Actions') }}</span></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->students as $student)
                    <flux:table.row :key="$student->id">
                        <flux:table.cell variant="strong">{{ $student->preferred_name ?? $student->first_name }}</flux:table.cell>
                        <flux:table.cell>{{ $student->last_name }}</flux:table.cell>
                        <flux:table.cell>{{ $student->pronouns }}</flux:table.cell>
                        <flux:table.cell align="end">
                            <flux:dropdown align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" :aria-label="__('Actions for :name', ['name' => $student->display_name])" />

                                <flux:menu>
                                    <flux:menu.item wire:click="showHistory({{ $student->id }})" icon="clock">{{ __('History') }}</flux:menu.item>
                                    <flux:menu.item wire:click="editStudent({{ $student->id }})" icon="pencil">{{ __('Edit details') }}</flux:menu.item>
                                    <flux:menu.item
                                        wire:click="withdrawStudent({{ $student->id }})"
                                        wire:confirm="{{ __('Mark :name as having left this class?', ['name' => $student->display_name]) }}"
                                        icon="user-minus"
                                        variant="danger"
                                    >{{ __('Mark as left') }}</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="4">{{ __('No students yet. Import a roster to get started.') }}</flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </section>

    @if ($this->formerStudents->isNotEmpty())
        <section class="space-y-4">
            <flux:heading size="lg" level="2">{{ __('Left the class') }}</flux:heading>

            <flux:table>
                <flux:table.rows>
                    @foreach ($this->formerStudents as $student)
                        <flux:table.row :key="'former-'.$student->id">
                            <flux:table.cell>{{ $student->display_name }}</flux:table.cell>
                            <flux:table.cell>{{ __('Left :date', ['date' => $student->deleted_at->toFormattedDateString()]) }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:button wire:click="restoreStudent({{ $student->id }})" size="sm" icon="arrow-uturn-left">{{ __('Restore') }}</flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </section>
    @endif

    <flux:modal name="student-history" class="w-full max-w-2xl">
        @if ($this->historyStudent)
            <div class="space-y-6">
                <flux:heading size="lg">{{ __('History for :name', ['name' => $this->historyStudent->display_name]) }}</flux:heading>

                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Date') }}</flux:table.column>
                        <flux:table.column>{{ __('Song') }}</flux:table.column>
                        <flux:table.column>{{ __('Result') }}</flux:table.column>
                        <flux:table.column>{{ __('Notes') }}</flux:table.column>
                        <flux:table.column><span class="sr-only">{{ __('Actions') }}</span></flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($this->historyStudent->attempts as $attempt)
                            <flux:table.row :key="'attempt-'.$attempt->id">
                                <flux:table.cell class="whitespace-nowrap">{{ $attempt->attempted_on->toFormattedDateString() }}</flux:table.cell>
                                <flux:table.cell>{{ $attempt->song->name }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge size="sm" :color="$attempt->passed ? 'green' : 'amber'">{{ $attempt->passed ? __('Passed') : __('Not yet') }}</flux:badge>
                                </flux:table.cell>
                                <flux:table.cell class="whitespace-normal">{{ $attempt->notes }}</flux:table.cell>
                                <flux:table.cell align="end">
                                    <flux:button
                                        wire:click="deleteAttempt({{ $attempt->id }})"
                                        wire:confirm="{{ __('Delete this attempt?') }}"
                                        variant="ghost"
                                        size="sm"
                                        icon="trash"
                                        :aria-label="__('Delete attempt')"
                                    />
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5">{{ __('No attempts yet.') }}</flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        @endif
    </flux:modal>

    <flux:modal name="edit-student" class="w-full max-w-md">
        @if ($this->editingStudent)
            <form wire:submit="saveStudent" class="space-y-6">
                <div>
                    <flux:heading size="lg">{{ $this->editingStudent->display_name }}</flux:heading>
                    @if ($this->editingStudent->preferred_name)
                        <flux:text class="mt-1">{{ __('Legal name: :name', ['name' => $this->editingStudent->first_name.' '.$this->editingStudent->last_name]) }}</flux:text>
                    @endif
                </div>

                <flux:input wire:model="preferredName" :label="__('Preferred name')" :description="__('Shown on the roster instead of their first name.')" />

                <flux:input wire:model="pronouns" :label="__('Pronouns')" placeholder="she/her, he/him, they/them" />

                <div class="flex justify-end gap-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                </div>
            </form>
        @endif
    </flux:modal>
</div>
