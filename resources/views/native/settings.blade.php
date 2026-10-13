<native:top-bar title="Settings" />

<native:scroll-view fill>
    <native:column class="w-full max-w-2xl p-6 gap-8 bg-theme-background">
        <native:column class="w-full gap-2">
            <native:text class="text-lg font-semibold text-theme-on-background">Your data</native:text>
            <native:text class="text-theme-on-surface-variant">Everything in Heartsong stays on this iPad. It isn't sent anywhere and isn't included in iCloud or device backups, so deleting the app deletes your data.</native:text>
        </native:column>

        <native:column class="w-full gap-3 p-4 rounded-xl bg-theme-surface">
            <native:text class="text-lg font-semibold text-theme-on-surface">End school year</native:text>
            <native:text class="text-theme-on-surface-variant">Permanently delete every student and their testing history. Classes and songs are kept so you can import next year's rosters.</native:text>
            <native:button label="End school year" icon="delete" variant="destructive" @press="confirmEndSchoolYear" />
        </native:column>
    </native:column>
</native:scroll-view>
