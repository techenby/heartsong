<native:top-bar title="Songs" :subtitle="$course->grade->value.' - '.$course->homeroom">
    <native:top-bar-action id="testing" label="Start testing" icon="play_arrow" ios-icon="play.fill" url="/courses/{{ $course->id }}/testing" />
</native:top-bar>

<native:column class="w-full h-full bg-theme-background">
    <native:column class="w-full p-4 gap-2">
        <native:text class="text-theme-on-surface-variant">Students work through these songs in order.</native:text>
        <native:row class="w-full gap-2 items-center">
            <native:column class="flex-1">
                <native:outlined-text-input placeholder="Song name, e.g. Hot Cross Buns" native:model="name" @submit="addSong" :is-error="isset($errors['name'])" />
            </native:column>
            <native:button label="Add song" icon="add" @press="addSong" />
        </native:row>
        @isset($errors['name'])
            <native:text class="text-sm text-theme-destructive">{{ $errors['name'][0] }}</native:text>
        @endisset
    </native:column>

    @if ($this->songs->isEmpty())
        <native:column class="w-full flex-1 items-center justify-center gap-3 p-8">
            <native:icon name="music" ios="music.note" android="music_note" :size="48" class="text-theme-on-surface-variant" />
            <native:text class="text-lg text-theme-on-surface-variant">No songs yet. Add the first song students will work on.</native:text>
        </native:column>
    @else
        <native:list fill separator>
            @foreach ($this->songs as $index => $song)
                <native:list-item
                    native:key="song-{{ $song->id }}"
                    :overline="'Song '.($index + 1)"
                    :headline="$song->name"
                    :supporting="trans_choice('{0} No attempts yet|{1} 1 attempt|[2,*] :count attempts', $song->attempts_count)"
                    leadingIconIos="music.note" leadingIconAndroid="music_note"
                    :trailing-menu="$this->songMenu($song->id)"
                />
            @endforeach
        </native:list>
    @endif
</native:column>

<native:bottom-sheet :visible="$showRename" @dismiss="closeRename" detents="small,medium">
    <native:column class="w-full p-6 gap-4">
        <native:text class="text-xl font-bold text-theme-on-surface">Rename song</native:text>
        <native:outlined-text-input label="Name" native:model="editingName" @submit="renameSong" :is-error="isset($errors['editingName'])" />
        @isset($errors['editingName'])
            <native:text class="text-sm text-theme-destructive">{{ $errors['editingName'][0] }}</native:text>
        @endisset
        <native:row class="w-full gap-2 justify-end">
            <native:button label="Cancel" variant="secondary" @press="closeRename" />
            <native:button label="Save" @press="renameSong" />
        </native:row>
    </native:column>
</native:bottom-sheet>
