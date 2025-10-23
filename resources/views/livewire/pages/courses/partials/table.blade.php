<flux:table>
    <flux:table.columns>
        <flux:table.column>Grade</flux:table.column>
        <flux:table.column>Homeroom</flux:table.column>
        <flux:table.column>Meets</flux:table.column>
        <flux:table.column># Students</flux:table.column>
        <flux:table.column><span class="sr-only">Actions</span></flux:table.column>
    </flux:table.columns>

    <flux:table.rows>
        @forelse ($this->courses as $course)
            <flux:table.row :key="keyFor($course)">
                <flux:table.cell class="flex gap-2 items-center">
                    <x-color-dot :background="$course->color->background()" />
                    <span>{{ $course->grade }}</span>
                </flux:table.cell>
                <flux:table.cell>{{ $course->homeroom }}</flux:table.cell>
                <flux:table.cell>{{ $course->meets }}</flux:table.cell>
                <flux:table.cell>{{ $course->students_count }}</flux:table.cell>
                <flux:table.cell>
                    <flux:dropdown>
                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom"></flux:button>

                        <flux:menu>
                            <flux:menu.item :href="route('courses.show', $course)" icon="eye">View</flux:menu.item>
                            <flux:menu.item href="#" icon="pencil">Edit</flux:menu.item>
                            <flux:menu.item href="#" icon="trash">Delete</flux:menu.item>
                        </flux:menu>
                    </flux:dropdown>
                </flux:table.cell>
            </flux:table.row>
        @empty
            <flux:table.row>
                <flux:table.cell colspan="2">
                    <flux:button :href="route('courses.create')">
                        Create New Course
                    </flux:button>
                </flux:table.cell>
            </flux:table.row>
        @endforelse
    </flux:table.rows>
</flux:table>
