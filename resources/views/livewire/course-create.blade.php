@php use App\Enum\Day;use App\Enum\Period; use Illuminate\Support\Carbon; @endphp
<div class="space-y-6">
    <flux:heading size="xl" level="1">Create Class</flux:heading>

    <form wire:submit="save" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <flux:input wire:model="name" name="name" label="Name" placeholder="8th Grade"/>

            <flux:radio.group wire:model="color" label="Color" variant="pills">
                <flux:radio value="red">
                    <span class="bg-red-400 border border-gray-100 rounded-full size-4"></span>
                    Red
                </flux:radio>
                <flux:radio value="orange">
                    <span class="bg-orange-400 border border-gray-100 rounded-full size-4"></span>
                    Orange
                </flux:radio>
                <flux:radio value="yellow">
                    <span class="bg-yellow-400 border border-gray-100 rounded-full size-4"></span>
                    Yellow
                </flux:radio>
                <flux:radio value="green">
                    <span class="bg-lime-400 border border-gray-100 rounded-full size-4"></span>
                    Green
                </flux:radio>
                <flux:radio value="blue">
                    <span class="bg-blue-400 border border-gray-100 rounded-full size-4"></span>
                    Blue
                </flux:radio>
                <flux:radio value="purple">
                    <span class="bg-purple-400 border border-gray-100 rounded-full size-4"></span>
                    Purple
                </flux:radio>
                <flux:radio value="white">
                    <span class="bg-white border border-gray-100 rounded-full size-4"></span>
                    White
                </flux:radio>
                <flux:radio value="black">
                    <span class="bg-black border border-gray-100 rounded-full size-4"></span>
                    Black
                </flux:radio>
                <flux:radio value="gray">
                    <span class="bg-gray-400 border border-gray-100 rounded-full size-4"></span>
                    Gray
                </flux:radio>
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
