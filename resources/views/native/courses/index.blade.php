@use('App\Enums\Day')

<native:top-bar title="Classes">
    <native:top-bar-action id="settings" label="Settings" icon="settings" url="/settings" />
    <native:top-bar-action id="create" label="Create class" icon="add" url="/courses/create" />
</native:top-bar>

<native:column class="w-full h-full bg-theme-background">
    @if ($this->courses->isEmpty())
        <native:column class="w-full h-full items-center justify-center gap-4 p-8">
            <native:icon name="music" ios="music.note" android="music_note" :size="48" class="text-theme-on-surface-variant" />
            <native:text class="text-lg text-theme-on-surface-variant">No classes yet.</native:text>
            <native:button label="Create class" icon="add" @navigate='/courses/create' />
        </native:column>
    @else
        <native:column class="w-full px-4 pt-2">
            <native:tab-row native:model="tab">
                <native:tab label="Calendar" icon="calendar" />
                <native:tab label="List" icon="list" />
            </native:tab-row>
        </native:column>

        @if ($tab === 0)
            <native:scroll-view fill>
                <native:column class="w-full p-4 gap-1">
                    <native:row class="w-full gap-1">
                        <native:text class="w-[110] text-sm font-semibold text-theme-on-surface-variant text-center">Times</native:text>
                        @foreach (Day::cases() as $day)
                            <native:text class="flex-1 text-sm font-semibold text-theme-on-surface-variant text-center">{{ $day->value }}</native:text>
                        @endforeach
                    </native:row>

                    @foreach ($this->periods as $period)
                        <native:row class="w-full gap-1" native:key="period-{{ $period->id }}">
                            <native:column class="w-[110] h-[64] items-center justify-center">
                                <native:text class="text-sm font-semibold text-theme-on-surface">{{ $period->name }}</native:text>
                                <native:text class="text-xs text-theme-on-surface-variant">{{ $period->formattedTimes() }}</native:text>
                            </native:column>

                            @foreach (Day::cases() as $day)
                                @php($meeting = $this->slots->get($day->value.'|'.$period->id))

                                @if ($meeting)
                                    <native:pressable
                                        @navigate="'/courses/' . $meeting->course_id"
                                        class="flex-1 h-[64] items-center justify-center rounded-lg {{ $meeting->course->color->background() }}"
                                        a11y-label="{{ $meeting->course->grade->value }} {{ $meeting->course->homeroom }}, {{ $day->value }} {{ $period->name }}"
                                    >
                                        <native:text class="text-sm font-semibold {{ $meeting->course->color->text() }}">{{ $meeting->course->formatted }}</native:text>
                                    </native:pressable>
                                @else
                                    <native:column class="flex-1 h-[64] rounded-lg bg-theme-surface-variant/40"></native:column>
                                @endif
                            @endforeach
                        </native:row>
                    @endforeach
                </native:column>
            </native:scroll-view>
        @else
            <native:list fill separator>
                @foreach ($this->courses as $course)
                    <native:list-item
                        native:key="course-{{ $course->id }}"
                        :headline="$course->grade->value.' - '.$course->homeroom"
                        :supporting="$course->meets"
                        :trailingText="trans_choice('{1} 1 student|[0,*] :count students', $course->students_count)"
                        leadingIconIos="music.note" leadingIconAndroid="music_note"
                        @press="open({{ $course->id }})"
                        :trailing-actions="[
                            ['method' => 'confirmDelete('.$course->id.')', 'label' => 'Delete', 'ios' => 'trash', 'android' => 'delete', 'role' => 'destructive'],
                        ]"
                    />
                @endforeach
            </native:list>
        @endif
    @endif
</native:column>
