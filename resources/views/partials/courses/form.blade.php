@php
    use App\Enums\Color;
    use App\Enums\Day;
    use App\Enums\Grade;
    use App\Enums\Period;
@endphp

<form wire:submit="save" class="space-y-6">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <flux:select wire:model="form.grade" :label="__('Grade')" :placeholder="__('Choose a grade...')">
            @foreach (Grade::cases() as $grade)
                <flux:select.option :value="$grade->value">{{ $grade->value }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input wire:model="form.homeroom" :label="__('Homeroom')" placeholder="A202" />
    </div>

    <flux:radio.group wire:model="form.color" :label="__('Color')" variant="pills">
        @foreach (Color::cases() as $color)
            <flux:radio :value="$color->value">
                <x-color-dot :$color />
                {{ $color->value }}
            </flux:radio>
        @endforeach
    </flux:radio.group>

    <div class="space-y-3">
        <flux:heading size="lg" level="2">{{ __('Times') }}</flux:heading>

        <flux:error name="form.meetings" :deep="false" />

        @foreach ($form->meetings as $index => $meeting)
            <flux:card size="sm" wire:key="meeting-{{ $index }}" class="grid grid-cols-1 items-start gap-4 md:grid-cols-[1fr_1fr_auto]">
                <flux:field>
                    <flux:select wire:model="form.meetings.{{ $index }}.day" :placeholder="__('Day of week')" :aria-label="__('Day of week')">
                        @foreach (Day::cases() as $day)
                            <flux:select.option :value="$day->value">{{ $day->value }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="form.meetings.{{ $index }}.day" />
                </flux:field>

                <flux:field>
                    <flux:select wire:model="form.meetings.{{ $index }}.period" :placeholder="__('Period')" :aria-label="__('Period')">
                        @foreach (Period::cases() as $period)
                            <flux:select.option :value="$period->value">{{ $period->value }} ({{ $period->formattedTimes() }})</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="form.meetings.{{ $index }}.period" />
                </flux:field>

                <div class="flex justify-end">
                    <flux:tooltip :content="__('Duplicate')">
                        <flux:button wire:click="duplicateMeeting({{ $index }})" variant="ghost" icon="square-2-stack" :aria-label="__('Duplicate')" />
                    </flux:tooltip>

                    <flux:tooltip :content="__('Remove')">
                        <flux:button wire:click="removeMeeting({{ $index }})" variant="ghost" icon="trash" :aria-label="__('Remove')" :disabled="count($form->meetings) === 1" />
                    </flux:tooltip>
                </div>
            </flux:card>
        @endforeach

        <flux:button icon="plus-circle" variant="ghost" wire:click="addMeeting">
            {{ __('Add another time') }}
        </flux:button>
    </div>

    <div class="flex gap-2">
        <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
        <flux:button :href="$cancelUrl" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
    </div>
</form>
