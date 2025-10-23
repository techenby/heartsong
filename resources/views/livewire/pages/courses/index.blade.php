<div class="space-y-6">
    <div class="flex justify-between gap-4">
        <flux:heading size="xl" level="1">Classes</flux:heading>

        <flux:button :href="route('courses.create')">
            Create
        </flux:button>
    </div>

    <flux:tab.group>
        <flux:tabs wire:model="tab" variant="segmented">
            <flux:tab name="calendar" icon="calendar-days">Calendar</flux:tab>
            <flux:tab name="list" icon="table-cells">Table</flux:tab>
        </flux:tabs>

        <flux:tab.panel name="calendar">
            @include('livewire.pages.courses.partials.calendar')
        </flux:tab.panel>
        <flux:tab.panel name="list">
            @include('livewire.pages.courses.partials.table')
        </flux:tab.panel>
    </flux:tab.group>
</div>
