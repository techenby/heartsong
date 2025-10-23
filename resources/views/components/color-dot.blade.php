@pure

@props([
    'background' => 'bg-white dark:bg-black',
    'size' => null,
])

@php
    $classes = Flux::classes()
        ->add($background)
        ->add('border border-zinc-200 dark:border-white/10')
        ->add('rounded-full')
        ->add(match ($size) {
            'xl' => 'size-12',
            default => 'size-4',
            'sm' => 'size-2',
        });
@endphp

<span {{ $attributes->class($classes) }} data-color-dot></span>
