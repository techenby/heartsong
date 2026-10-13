<native:top-bar :title="$this->navTitle()" :subtitle="$course->meets">
    <native:top-bar-action id="import" label="Import roster" ios-icon="square.and.arrow.down" android-icon="file_upload" @tap="openImport" />
    <native:top-bar-action id="songs" label="Songs" ios-icon="music.note.list" android-icon="queue_music" url="/courses/{{ $course->id }}/songs" />
    <native:top-bar-action id="edit" label="Edit class" icon="edit" url="/courses/{{ $course->id }}/edit" />
</native:top-bar>

<native:column class="w-full h-full bg-theme-background">
    <native:row class="w-full px-4 pt-3 pb-1 gap-3 items-center">
        <native:column class="w-[16] h-[16] rounded-full {{ $course->color->background() }}"></native:column>
        <native:text class="flex-1 text-lg font-semibold text-theme-on-background">Roster</native:text>
        <native:button label="Start testing" icon="play_arrow" ios-icon="play.fill" @navigate="'/courses/' . $course->id . '/testing'" />
    </native:row>

    @if ($this->students->isEmpty())
        @include('native.partials.empty-state', ['icon' => 'people', 'ios' => 'person.3', 'android' => 'groups', 'message' => 'No students yet. Import a roster to get started.', 'button' => 'Import roster', 'press' => 'openImport'])
    @else
        <native:list fill separator>
            @foreach ($this->students as $student)
                <native:list-item
                    native:key="student-{{ $student->id }}"
                    :headline="$student->display_name"
                    :supporting="$student->pronouns"
                    leadingIcon="person"
                    @press="openHistory({{ $student->id }})"
                    :trailing-menu="$this->studentMenu($student->id)"
                />
            @endforeach
        </native:list>
    @endif
</native:column>

<native:bottom-sheet :visible="$editingStudentId !== null" @dismiss="closeDetails" detents="medium">
    @if ($editingStudent = $editingStudentId ? $this->students->firstWhere('id', $editingStudentId) : null)
        <native:column class="w-full p-6 gap-4">
            <native:column class="gap-1">
                <native:text class="text-xl font-bold text-theme-on-surface">{{ $editingStudent->display_name }}</native:text>
                @if ($editingStudent->preferred_name)
                    <native:text class="text-sm text-theme-on-surface-variant">Legal name: {{ $editingStudent->first_name }} {{ $editingStudent->last_name }}</native:text>
                @endif
            </native:column>

            <native:outlined-text-input label="Preferred name" supporting="Shown on the roster instead of their first name." native:model="preferredName"
                :is-error="(bool) $this->error('preferredName')" />
            @include('native.partials.field-error', ['field' => 'preferredName'])

            <native:outlined-text-input label="Pronouns" placeholder="she/her, he/him, they/them" native:model="pronouns"
                :is-error="(bool) $this->error('pronouns')" />
            @include('native.partials.field-error', ['field' => 'pronouns'])

            <native:row class="w-full gap-2 justify-end">
                <native:button label="Cancel" variant="secondary" @press="closeDetails" />
                <native:button label="Save" @press="saveStudent" />
            </native:row>
        </native:column>
    @endif
</native:bottom-sheet>

<native:bottom-sheet :visible="$historyStudentId !== null" @dismiss="closeHistory" detents="medium,large">
    @if ($this->historyStudent)
        <native:column class="w-full h-full">
            <native:text class="text-xl font-bold text-theme-on-surface p-6 pb-2">History for {{ $this->historyStudent->display_name }}</native:text>

            @if ($this->historyStudent->attempts->isEmpty())
                <native:text class="px-6 text-theme-on-surface-variant">No attempts yet.</native:text>
            @else
                <native:list fill separator>
                    @foreach ($this->historyStudent->attempts as $attempt)
                        <native:list-item
                            native:key="attempt-{{ $attempt->id }}"
                            :headline="$attempt->song->name"
                            :supporting="$attempt->attempted_on->toFormattedDateString().($attempt->notes ? ' · '.$attempt->notes : '')"
                            :trailingText="$attempt->passed ? 'Passed' : 'Not yet'"
                            :leadingIcon="$attempt->passed ? 'check' : 'refresh'"
                            :trailing-actions="[
                                ['method' => 'deleteAttempt('.$attempt->id.')', 'label' => 'Delete', 'ios' => 'trash', 'android' => 'delete', 'role' => 'destructive'],
                            ]"
                        />
                    @endforeach
                </native:list>
            @endif
        </native:column>
    @endif
</native:bottom-sheet>

<native:bottom-sheet :visible="$showImport" @dismiss="closeImport" detents="medium,large">
    <native:column class="w-full h-full p-6 gap-4">
        @if ($importNames === [])
            <native:text class="text-xl font-bold text-theme-on-surface">Import roster</native:text>
            <native:text class="text-theme-on-surface-variant">Choose a CSV or Excel file with a Name column of First, Last. Students already in this class keep their preferred names and pronouns. You'll be asked about anyone missing from the file.</native:text>

            @if ($importError)
                <native:text class="text-sm text-theme-destructive">{{ $importError }}</native:text>
            @endif

            <native:row class="w-full gap-2 justify-end">
                <native:button label="Cancel" variant="secondary" @press="closeImport" />
                <native:button label="Choose file" icon="folder" @press="chooseRoster" />
            </native:row>
        @else
            <native:text class="text-xl font-bold text-theme-on-surface">
                {{ trans_choice("{1} 1 student isn't in this roster|[2,*] :count students aren't in this roster", $this->missingStudents->count()) }}
            </native:text>
            <native:text class="text-theme-on-surface-variant">Check anyone who has left the class. They will be permanently deleted along with their testing history. Anyone left unchecked stays in the class.</native:text>

            <native:column class="w-full flex-1">
            <native:list fill separator plain>
                @foreach ($this->missingStudents as $student)
                    <native:list-item
                        native:key="missing-{{ $student->id }}"
                        :headline="$student->display_name"
                        :leadingCheckbox="in_array($student->id, $withdraw, true)"
                        on-leading-change="toggleWithdraw({{ $student->id }})"
                    />
                @endforeach
            </native:list>
            </native:column>

            <native:row class="w-full gap-2 justify-end">
                <native:button label="Back" variant="secondary" @press="openImport" />
                <native:button label="Import" @press="import" />
            </native:row>
        @endif
    </native:column>
</native:bottom-sheet>
