@props(['meetings'])

@php
    use App\Enums\Day;
    use App\Enums\Period;

    $slots = $meetings->keyBy(fn ($meeting) => $meeting->day->value.'|'.$meeting->period->value);
@endphp

<flux:table>
    <flux:table.columns>
        <flux:table.column align="center">Times</flux:table.column>
        @foreach (Day::cases() as $day)
            <flux:table.column align="center">{{ $day->value }}</flux:table.column>
        @endforeach
    </flux:table.columns>

    <flux:table.rows>
        @foreach (Period::cases() as $period)
            <flux:table.row :key="'period-'.$period->name">
                <flux:table.cell align="center" class="whitespace-nowrap">{{ $period->formattedTimes() }}</flux:table.cell>

                @foreach (Day::cases() as $day)
                    @php($meeting = $slots->get($day->value.'|'.$period->value))

                    @if ($meeting)
                        <flux:table.cell variant="strong" align="center" class="{{ $meeting->course->color->background() }} border-l border-zinc-100 dark:border-white/10">
                            <flux:link :href="route('courses.show', $meeting->course)" variant="ghost" class="{{ $meeting->course->color->text() }}" wire:navigate>
                                {{ $meeting->course->formatted }}
                            </flux:link>
                        </flux:table.cell>
                    @else
                        <flux:table.cell></flux:table.cell>
                    @endif
                @endforeach
            </flux:table.row>
        @endforeach
    </flux:table.rows>
</flux:table>
