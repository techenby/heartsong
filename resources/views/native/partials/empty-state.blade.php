{{-- Expects $icon, $ios, $android, $message; optional $button (label), $buttonIcon, and $press (method) or $navigate (path). --}}
<native:column class="w-full flex-1 items-center justify-center gap-4 p-8 bg-theme-background">
    <native:icon :name="$icon" :ios="$ios" :android="$android" :size="48" class="text-theme-on-surface-variant" />
    <native:text class="text-lg text-theme-on-surface-variant text-center">{{ $message }}</native:text>

    @isset($button)
        @isset($navigate)
            <native:button :label="$button" :icon="$buttonIcon ?? null" @navigate="$navigate" />
        @else
            <native:button :label="$button" :icon="$buttonIcon ?? null" @press="{{ $press }}" />
        @endisset
    @endisset
</native:column>
