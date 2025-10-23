<form wire:submit="import" class="space-y-2">
    <flux:file-upload wire:model="roster" label="Import roster">
        <flux:file-upload.dropzone heading="Drop file here or click to browse" text="CSV up to 1MB" inline/>
    </flux:file-upload>

    @if ($roster)
        <flux:file-item
            :heading="$roster->getClientOriginalName()"
            :size="$roster->getSize()"
        >
            <x-slot name="actions">
                <flux:file-item.remove wire:click="remove" aria-label="{{ 'Remove file: ' . $roster->getClientOriginalName() }}" />
            </x-slot>
        </flux:file-item>
    @endif
    <flux:button type="submit">Import</flux:button>
</form>
