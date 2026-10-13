<native:top-bar title="Testing" :subtitle="$course->name.' · '.today()->toFormattedDayDateString()">
    <native:top-bar-action id="songs" label="Songs" ios-icon="music.note.list" android-icon="queue_music" url="/courses/{{ $course->id }}/songs" />
</native:top-bar>

@if ($this->songs->isEmpty())
    @include('native.partials.empty-state', ['icon' => 'music', 'ios' => 'music.note', 'android' => 'music_note', 'message' => 'Add songs to start testing.', 'button' => 'Add songs', 'buttonIcon' => 'add', 'navigate' => '/courses/'.$course->id.'/songs'])
@elseif ($this->students->isEmpty())
    @include('native.partials.empty-state', ['icon' => 'people', 'ios' => 'person.3', 'android' => 'groups', 'message' => 'Import a roster to start testing.', 'button' => 'Go to roster', 'press' => 'back'])
@else
    <native:column class="w-full h-full md:flex-row gap-4 p-4 bg-theme-background">
        <native:scroll-view class="w-full md:flex-1">
            <native:column class="w-full gap-5 p-6 rounded-2xl bg-theme-surface">
                @if ($this->student)
                    <native:column class="gap-1">
                        <native:text class="text-sm text-theme-on-surface-variant">{{ $selectedStudentId ? 'Testing' : 'Up next' }}</native:text>
                        <native:text class="text-3xl font-bold text-theme-on-surface">{{ $this->student->display_name }}</native:text>
                        @if ($this->student->pronouns)
                            <native:text class="text-theme-on-surface-variant">{{ $this->student->pronouns }}</native:text>
                        @endif
                    </native:column>

                    <native:select label="Song" :options="$this->songOptions()" native:model="songChoice" />

                    @if ($this->song)
                        <native:text class="text-theme-on-surface-variant">
                            {{ trans_choice('{0} First try on :song|{1} 1 previous try on :song|[2,*] :count previous tries on :song', $this->student->triesOn($this->song), ['song' => $this->song->name]) }}
                        </native:text>

                        <native:outlined-text-input label="Notes" placeholder="Optional" native:model="notes" multiline :min-lines="2" />

                        <native:row class="w-full gap-3">
                            <native:column class="flex-1">
                                <native:button label="Passed" icon="check" size="lg" @press="record(true)" />
                            </native:column>
                            <native:column class="flex-1">
                                <native:button label="Not yet" icon="refresh" size="lg" variant="secondary" @press="record(false)" />
                            </native:column>
                        </native:row>
                    @else
                        <native:text class="text-theme-on-surface-variant">{{ $this->student->display_name }} has passed every song. Choose a song to test again.</native:text>
                    @endif
                @else
                    <native:text class="text-xl font-semibold text-theme-on-surface">Everyone has been tested today</native:text>
                    <native:text class="text-theme-on-surface-variant">Pick a student from the list to test them again.</native:text>
                @endif
            </native:column>
        </native:scroll-view>

        <native:column class="w-full flex-1 gap-2">
            <native:text class="text-lg font-semibold text-theme-on-background">Testing order</native:text>
            <native:list fill separator>
                @foreach ($this->students as $student)
                    @php($todaysAttempt = $this->todaysAttempts->get($student->id))

                    <native:list-item
                        native:key="student-{{ $student->id }}"
                        :headline="$student->display_name"
                        :supporting="$student->currentSong($this->songs)?->name ?? 'All songs passed'"
                        :trailingText="$todaysAttempt ? ($todaysAttempt->passed ? 'Passed' : 'Not yet') : ''"
                        :leadingIcon="$this->student?->is($student) ? 'arrow_forward' : 'person'"
                        @press="selectStudent({{ $student->id }})"
                        :trailing-menu="$this->studentMenu($student->id)"
                    />
                @endforeach
            </native:list>
        </native:column>
    </native:column>
@endif
