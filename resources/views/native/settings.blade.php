<native:top-bar title="Settings" />

<native:list fill class="bg-theme-background">
    <native:list-section header="Bell schedule" footer="Classes are scheduled into these periods. Changing a period's times moves every class that meets during it.">
        @foreach ($this->periods as $period)
            <native:list-item
                native:key="period-{{ $period->id }}"
                :headline="$period->name"
                :supporting="$period->formattedTimes(withMeridiem: true)"
                :trailingText="trans_choice('{0} |{1} 1 class time|[2,*] :count class times', $period->meetings_count)"
                @press="editPeriod({{ $period->id }})"
                :trailing-menu="$this->periodMenu($period->id)"
            />
        @endforeach

        <native:list-item
            headline="Add period"
            leadingIcon="add"
            :headlineColor="theme('primary')"
            :leadingIconColor="theme('primary')"
            @press="addPeriod"
        />
    </native:list-section>

    <native:list-section header="Your data" footer="Heartsong isn't sent anywhere and isn't included in iCloud or device backups, so deleting the app deletes your data.">
        <native:list-item
            headline="Stored only on this iPad"
            leadingIcon="lock"
        />
    </native:list-section>

    <native:list-section header="School year" footer="Permanently deletes every student and their testing history. Classes and songs are kept so you can import next year's rosters.">
        <native:list-item
            headline="End school year"
            leadingIcon="delete"
            :headlineColor="theme('destructive')"
            :leadingIconColor="theme('destructive')"
            @press="confirmEndSchoolYear"
        />
    </native:list-section>
</native:list>

<native:bottom-sheet :visible="$showPeriod" @dismiss="closePeriod" detents="medium">
    <native:column class="w-full p-6 gap-4">
        <native:text class="text-xl font-bold text-theme-on-surface">{{ $editingPeriodId ? 'Edit period' : 'Add period' }}</native:text>

        <native:outlined-text-input label="Name" placeholder="1st Period" native:model="periodName" :is-error="(bool) $this->error('name')" />
        @include('native.partials.field-error', ['field' => 'name'])

        <native:row class="w-full gap-4">
            <native:column class="flex-1">
                <native:date-picker label="Starts" mode="time" native:model="startsAt" />
            </native:column>
            <native:column class="flex-1">
                <native:date-picker label="Ends" mode="time" native:model="endsAt" :is-error="(bool) $this->error('ends_at')" />
            </native:column>
        </native:row>
        @include('native.partials.field-error', ['field' => 'starts_at'])
        @include('native.partials.field-error', ['field' => 'ends_at'])

        <native:row class="w-full gap-2 justify-end">
            <native:button label="Cancel" variant="secondary" @press="closePeriod" />
            <native:button label="Save" @press="savePeriod" />
        </native:row>
    </native:column>
</native:bottom-sheet>
