<div class="space-y-5">
    <div>
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>
    </div>

    <div class="w-full max-w-lg">
        {{ $slot }}
    </div>
</div>
