@php use App\Enum\Color;use App\Enum\Day;use App\Enum\Grade;use App\Enum\Period; use Illuminate\Support\Carbon; @endphp
<form wire:submit="save" class="space-y-4">
    <div class="space-y-2">
        <flux:heading size="lg" level="2">Times</flux:heading>

        @foreach ($meetings as $index => $meeting)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:select wire:model="meetings.{{ $index }}.day" name="day" placeholder="Day of Week">
                    @foreach (Day::cases() as $day)
                        <flux:select.option :value="$day->name">{{ $day->value }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="meetings.{{ $index }}.period" name="period" placeholder="Period">
                    @foreach (Period::cases() as $period)
                        <flux:select.option :value="$period->name">{{ $period->value }}</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="flex justify-end">
                    <flux:button wire:click="remove({{ $index }})" variant="ghost">
                        <flux:icon.trash/>
                        <span class="sr-only">Trash</span>
                    </flux:button>
                </div>
            </div>
        @endforeach

        <flux:button icon="plus-circle" variant="ghost" wire:click="add">
            Add new time
        </flux:button>
    </div>

    <flux:button type="submit" variant="primary">Save</flux:button>
</form>
