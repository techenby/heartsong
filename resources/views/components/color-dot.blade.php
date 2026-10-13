@props([
    'color' => null,
    'size' => null,
])

@php
    $classes = Flux::classes()
        ->add($color?->background() ?? 'bg-white dark:bg-black')
        ->add('inline-block shrink-0 rounded-full border border-zinc-200 dark:border-white/10')
        ->add(match ($size) {
            'xl' => 'size-12',
            'sm' => 'size-2',
            default => 'size-4',
        });
@endphp

<span {{ $attributes->class($classes) }} data-color-dot></span>
