<div>
    <flux:heading size="xl" level="1">Courses</flux:heading>

    <flux:table :paginate="$this->courses">
        <flux:table.columns>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>Meets</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->courses as $course)
            <flux:table.row :key="keyFor($course)">
                <flux:table.cell>{{ $course->name }}</flux:table.cell>
                <flux:table.cell>{{ $course->meets }}</flux:table.cell>
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
</div>
