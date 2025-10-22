@php use App\Enum\Color;use App\Enum\Day;use App\Enum\Grade;use App\Enum\Period; use Illuminate\Support\Carbon; @endphp
<div class="space-y-6">
    <flux:heading size="xl" level="1">Create Class</flux:heading>

    <form wire:submit="save" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <flux:select wire:model="grade" name="grade" label="Grade" placeholder="Grade">
                @foreach (Grade::cases() as $grade)
                    <flux:select.option :value="$grade->name">{{ $grade->value }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:radio.group wire:model="color" label="Color" variant="pills">
                @foreach (Color::cases() as $color)
                    <flux:radio :value="$color->name">
                        <span class="{{ $color->background() }} border border-gray-100 rounded-full size-4"></span>
                        {{ $color->value }}
                    </flux:radio>
                @endforeach
            </flux:radio.group>

            <flux:input wire:model="homeroom" name="homeroom" label="Homeroom" placeholder="A202"/>
        </div>


        <div class="space-y-2">
            <flux:heading size="lg" level="2">Times</flux:heading>

            @foreach ($meetings as $index => $meeting)
                <flux:card class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                        <flux:tooltip content="Duplicate">
                            <flux:button wire:click="duplicate({{ $index }})" variant="ghost">
                                <flux:icon.square-2-stack/>
                                <span class="sr-only">Duplicate</span>
                            </flux:button>
                        </flux:tooltip>

                        <flux:tooltip content="Trash">
                            <flux:button wire:click="remove({{ $index }})" variant="ghost">
                                <flux:icon.trash/>
                                <span class="sr-only">Trash</span>
                            </flux:button>
                        </flux:tooltip>
                    </div>
                </flux:card>
            @endforeach

            <flux:button icon="plus-circle" variant="ghost" wire:click="add">
                Add new time
            </flux:button>
        </div>

        <flux:button type="submit" variant="primary">Save</flux:button>
    </form>
</div>
