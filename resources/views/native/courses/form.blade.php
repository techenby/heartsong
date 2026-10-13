@use('App\Enums\Color')
@use('App\Enums\Day')
@use('App\Enums\Grade')
@use('App\Enums\Period')

<native:top-bar :title="$this->navTitle()">
    <native:top-bar-action id="save" label="Save" icon="check" @tap="save" />
</native:top-bar>

<native:scroll-view fill>
    <native:column class="w-full max-w-3xl p-6 gap-6">
        <native:row class="w-full gap-4">
            <native:column class="flex-1 gap-1">
                <native:select label="Grade" placeholder="Choose a grade" :options="array_map(fn ($grade) => $grade->value, Grade::cases())" native:model="grade" />
                @if ($error = $this->error('grade'))
                    <native:text class="text-sm text-theme-destructive">{{ $error }}</native:text>
                @endif
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
            @if ($error = $this->error('color'))
                <native:text class="text-sm text-theme-destructive">{{ $error }}</native:text>
            @endif
        </native:column>

        <native:column class="w-full gap-3">
            <native:text class="text-lg font-semibold text-theme-on-background">Times</native:text>

            @foreach ($meetings as $index => $meeting)
                <native:column class="w-full gap-2 p-4 rounded-xl bg-theme-surface" native:key="meeting-{{ $index }}">
                    <native:row class="w-full gap-3 items-center">
                        <native:column class="flex-1">
                            <native:select label="Day" placeholder="Day of week" :options="array_map(fn ($day) => $day->value, Day::cases())" native:model="meetings.{{ $index }}.day" />
                        </native:column>
                        <native:column class="flex-1">
                            <native:select label="Period" placeholder="Period" :options="array_map(fn ($period) => $period->value, Period::cases())" native:model="meetings.{{ $index }}.period" />
                        </native:column>
                        <native:button label="Duplicate" ios-icon="doc.on.doc" android-icon="content_copy" variant="ghost" @press="duplicateMeeting({{ $index }})" />
                        <native:button label="Remove" icon="delete" variant="ghost" :disabled="count($meetings) === 1" @press="removeMeeting({{ $index }})" />
                    </native:row>

                    @foreach (['day', 'period'] as $field)
                        @if ($error = $this->error("meetings.{$index}.{$field}"))
                            <native:text class="text-sm text-theme-destructive">{{ $error }}</native:text>
                        @endif
                    @endforeach
                </native:column>
            @endforeach

            <native:button label="Add another time" icon="add" variant="secondary" @press="addMeeting" />
        </native:column>

        <native:button label="Save" icon="check" @press="save" />
    </native:column>
</native:scroll-view>
