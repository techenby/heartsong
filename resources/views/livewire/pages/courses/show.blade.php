<div class="space-y-6">
    <div class="flex gap-4 items-center">
        <x-color-dot :background="$course->color->background()" size="xl" />
        <div>
            <flux:heading size="xl" level="1">{{ $course->grade }} - {{ $course->homeroom }}</flux:heading>
            <flux:subheading size="lg">{{ $course->meets }}</flux:subheading>
        </div>
    </div>

    <section id="roster" class="space-y-4">
        <div class="flex justify-between gap-4">
            <flux:heading size="lg" level="2">Roster</flux:heading>

            @if (! empty($course->students))
                <flux:modal.trigger name="import">
                    <flux:button size="xs" class="ml-auto">Reimport</flux:button>
                </flux:modal.trigger>

                <flux:modal name="import" class="md:w-96 space-y-4">
                    <livewire:components.import-roster :$course/>

                    <flux:callout variant="warning" icon="exclamation-circle" heading="This will delete all students from this course and reimport them." />
                </flux:modal>
            @endif
        </div>

        <flux:card size="xs" class="divide-y divide-zinc-200 dark:divide-white/10 py-0">
            @forelse ($course->students as $student)
                <div :key="keyFor($student)" class="grid grid-cols-6 py-3 px-4 gap-4">
                    <div>{{ $student->preferred_name ?? $student->first_name }}</div>
                    <div>{{ $student->last_name }}</div>
                    <div>{{ $student->pronouns }}</div>
                    <div></div>
                </div>
            @empty
                <div class="px-4 py-3 max-w-md mx-auto">
                    <livewire:components.import-roster :$course />
                </div>
            @endforelse
        </flux:card>
    </section>

</div>
