<native:column class="w-full h-full items-center justify-center gap-6 p-8 bg-theme-background safe-area">
    <native:icon name="lock" :size="56" class="text-theme-primary" />

    <native:column class="items-center gap-2">
        <native:text class="text-3xl font-bold text-theme-on-background">Heartsong</native:text>
        <native:text class="text-base text-theme-on-surface-variant text-center">Unlock to see your classes.</native:text>
    </native:column>

    @if ($failed)
        <native:text class="text-sm text-theme-destructive text-center">Couldn't unlock. Make sure Touch ID is set up in the iPad's Settings, then try again.</native:text>
    @endif

    <native:button label="Unlock" icon="lock" @press="unlock" />
</native:column>
