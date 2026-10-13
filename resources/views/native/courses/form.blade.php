@use('App\Enums\Color')
@use('App\Enums\Day')
@use('App\Enums\Grade')

<native:top-bar :title="$this->navTitle()">
    <native:top-bar-action id="save" label="Save" icon="check" @tap="save" />
</native:top-bar>

<native:scroll-view fill>
    <native:column class="w-full max-w-3xl p-6 gap-6">
        <native:row class="w-full gap-4">
            <native:column class="flex-1 gap-1">
                <native:select label="Grade" placeholder="Choose a grade" :options="array_column(Grade::cases(), 'value')" native:model="grade" />
                @include('native.partials.field-error', ['field' => 'grade'])
            </native:column>

            <native:column class="flex-1 gap-1">
                <native:outlined-text-input label="Homeroom" placeholder="A202" native:model="homeroom" :is-error="(bool) $this->error('homeroom')" :supporting="$this->error('homeroom')" />
            </native:column>
        </native:row>

        <native:column class="w-full gap-2">
            <native:text class="text-base font-semibold text-theme-on-background">Color</native:text>
            <native:row class="w-full gap-2 flex-wrap">
                @foreach (Color::cases() as $option)
                    <native:chip :label="$option->value" :selected="$color === $option->value" @change="setColor('{{ $option->value }}')" />
                @endforeach
            </native:row>
            @include('native.partials.field-error', ['field' => 'color'])
        </native:column>

        <native:column class="w-full gap-2">
            <native:text class="text-lg font-semibold text-theme-on-background">Times</native:text>

            @foreach ($meetings as $index => $meeting)
                <native:column class="w-full gap-1" native:key="meeting-{{ $index }}">
                    <native:row class="w-full gap-3 items-center">
                        <native:column class="flex-1">
                            <native:select placeholder="Day of week" a11y-label="Day of week" :options="array_column(Day::cases(), 'value')" value="{{ $meeting['day'] }}" @change="setMeeting({{ $index }}, 'day')" />
                        </native:column>
                        <native:column class="flex-1">
                            <native:select placeholder="Period" a11y-label="Period" :options="$this->periods->pluck('name')->all()" value="{{ $this->periodName($meeting['period_id']) }}" @change="setMeeting({{ $index }}, 'period')" />
                        </native:column>
                        <native:button label="Duplicate" ios-icon="doc.on.doc" android-icon="content_copy" variant="ghost" @press="duplicateMeeting({{ $index }})" />
                        <native:button label="Remove" icon="delete" variant="ghost" :disabled="count($meetings) === 1" @press="removeMeeting({{ $index }})" />
                    </native:row>

                    @include('native.partials.field-error', ['field' => "meetings.{$index}.day"])
                    @include('native.partials.field-error', ['field' => "meetings.{$index}.period_id"])
                </native:column>
            @endforeach

            <native:button label="Add another time" icon="add" variant="secondary" @press="addMeeting" />
        </native:column>

        <native:button label="Save" icon="check" @press="save" />
    </native:column>
</native:scroll-view>
