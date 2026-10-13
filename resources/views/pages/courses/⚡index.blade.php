<?php

use App\Enums\Grade;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Classes')] class extends Component {
    #[Url]
    public string $tab = 'calendar';

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

    public function delete(int $courseId): void
    {
        Course::findOrFail($courseId)->delete();

        unset($this->courses);
    }
}; ?>

<div class="space-y-6">
    <div class="flex items-center justify-between gap-4">
        <flux:heading size="xl" level="1">{{ __('Classes') }}</flux:heading>

        <flux:button :href="route('courses.create')" icon="plus" variant="primary" wire:navigate>
            {{ __('Create class') }}
        </flux:button>
    </div>

    <flux:tab.group>
        <flux:tabs wire:model="tab" variant="segmented">
            <flux:tab name="calendar" icon="calendar-days">{{ __('Calendar') }}</flux:tab>
            <flux:tab name="table" icon="table-cells">{{ __('Table') }}</flux:tab>
        </flux:tabs>

        <flux:tab.panel name="calendar">
            <x-courses.calendar :meetings="$this->courses->flatMap->meetings" />
        </flux:tab.panel>

        <flux:tab.panel name="table">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Grade') }}</flux:table.column>
                    <flux:table.column>{{ __('Homeroom') }}</flux:table.column>
                    <flux:table.column>{{ __('Meets') }}</flux:table.column>
                    <flux:table.column>{{ __('Students') }}</flux:table.column>
                    <flux:table.column><span class="sr-only">{{ __('Actions') }}</span></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->courses as $course)
                        <flux:table.row :key="$course->id">
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <x-color-dot :color="$course->color" />
                                    <flux:link :href="route('courses.show', $course)" variant="ghost" wire:navigate>{{ $course->grade->value }}</flux:link>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>{{ $course->homeroom }}</flux:table.cell>
                            <flux:table.cell>{{ $course->meets }}</flux:table.cell>
                            <flux:table.cell>{{ $course->students_count }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <flux:dropdown align="end">
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" :aria-label="__('Actions')" />

                                    <flux:menu>
                                        <flux:menu.item :href="route('courses.show', $course)" icon="eye" wire:navigate>{{ __('View') }}</flux:menu.item>
                                        <flux:menu.item :href="route('courses.edit', $course)" icon="pencil" wire:navigate>{{ __('Edit') }}</flux:menu.item>
                                        <flux:menu.item
                                            wire:click="delete({{ $course->id }})"
                                            wire:confirm="{{ __('Delete :class? Its schedule and roster will be deleted too.', ['class' => $course->formatted]) }}"
                                            icon="trash"
                                            variant="danger"
                                        >{{ __('Delete') }}</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="5">{{ __('No classes yet.') }}</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:tab.panel>
    </flux:tab.group>
</div>
