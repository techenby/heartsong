@php use App\Enum\Day;use App\Enum\Period; @endphp
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
                        <flux:table.cell variant="strong" align="center" :key="keyFor($meeting)" class="{{ $meeting->course->color->background() }} border-l border-zinc-100">
                            <flux:link :href="route('courses.show', $meeting->course)" variant="ghost" class="{{ $meeting->course->color->text() }}">{{ $meeting->course->formatted ?? '' }}</flux:link>
                        </flux:table.cell>
                    @else
                        <flux:table.cell></flux:table.cell>
                    @endif
                @endforeach
            </flux:table.row>
        @endforeach
    </flux:table.rows>
</flux:table>
