@php use App\Enum\Day;use App\Enum\Period; @endphp
<div class="space-y-6">
    <div class="flex justify-between gap-4">
        <flux:heading size="xl" level="1">Classes</flux:heading>

        <flux:button :href="route('courses.create')">
            Create
        </flux:button>
    </div>

    <flux:tab.group wire:cloak>
        <flux:tabs wire:model="tab" variant="segmented">
            <flux:tab name="calendar" icon="calendar-days">Calendar</flux:tab>
            <flux:tab name="list" icon="table-cells">Table</flux:tab>
        </flux:tabs>

        <flux:tab.panel name="calendar">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column align="center">Times</flux:table.column>
                    @foreach (Day::cases() as $day)
                        <flux:table.column align="center">{{ $day->value }}</flux:table.column>
                    @endforeach
                </flux:table.columns>

                {{-- TODO: Nasty, move to component --}}
                <flux:table.rows>
                    @foreach (Period::cases() as $period)
                        <flux:table.row :key="'period-row-' . $period->name">
                            <flux:table.cell align="center">{{ $period->formattedTimes() }}</flux:table.cell>
                            @foreach (Day::cases() as $day)
                                @php
                                    $meeting = $this->meetings->where('period', $period)->where('day', $day)->first();
                                @endphp

                                @if ($meeting)
                                    <flux:table.cell variant="strong" align="center" :key="keyFor($meeting)" class="{{ $meeting->course->color->background() }} {{ $meeting->course->color->text() }} border-l border-zinc-100">
                                        {{ $meeting->course->formatted ?? '' }}
                                    </flux:table.cell>
                                @else
                                    <flux:table.cell></flux:table.cell>
                                @endif
                            @endforeach
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:tab.panel>
        <flux:tab.panel name="list">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Grade</flux:table.column>
                    <flux:table.column>Homeroom</flux:table.column>
                    <flux:table.column>Meets</flux:table.column>
                    <flux:table.column># Students</flux:table.column>
                    <flux:table.column><span class="sr-only">Actions</span></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->courses as $course)
                        <flux:table.row :key="keyFor($course)">
                            <flux:table.cell class="flex gap-2 items-center">
                                <x-color-dot :background="$course->color->background()" />
                                <span>{{ $course->grade }}</span>
                            </flux:table.cell>
                            <flux:table.cell>{{ $course->homeroom }}</flux:table.cell>
                            <flux:table.cell>{{ $course->meets }}</flux:table.cell>
                            <flux:table.cell>{{ $course->students_count }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>

                                    <flux:menu>
                                        <flux:menu.item :href="route('courses.show', $course)" icon="eye">View</flux:menu.item>
                                        <flux:menu.item href="#" icon="pencil">Edit</flux:menu.item>
                                        <flux:menu.item href="#" icon="trash">Delete</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="2">
                                <flux:button :href="route('courses.create')">
                                    Create New Course
                                </flux:button>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:tab.panel>
    </flux:tab.group>
</div>
