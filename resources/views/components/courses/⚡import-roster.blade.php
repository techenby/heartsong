<?php

use App\Actions\ImportRoster;
use App\Models\Course;
use App\Models\Student;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public Course $course;

    #[Validate(['required', 'file', 'mimes:csv,txt,xlsx', 'max:1024'])]
    public ?TemporaryUploadedFile $roster = null;

    /** @var list<array{0: string, 1: string}> Names read from the uploaded roster, waiting on confirmation. */
    #[Locked]
    public array $names = [];

    /** @var array<int, int|string> Missing students the teacher has marked as having left. */
    public array $withdraw = [];

    /**
     * @return Collection<int, Student>
     */
    #[Computed]
    public function missingStudents(): Collection
    {
        return $this->names === []
            ? collect()
            : app(ImportRoster::class)->missingStudents($this->course, $this->names);
    }

    public function import(ImportRoster $importRoster): void
    {
        $this->validate();

        $type = strtolower($this->roster->getClientOriginalExtension()) === 'xlsx' ? 'xlsx' : 'csv';
        $this->names = $importRoster->readNames($this->roster->getRealPath(), $type);
        $this->removeRoster();
        unset($this->missingStudents);

        if ($this->missingStudents->isEmpty()) {
            $this->confirm($importRoster);
        }
    }

    public function confirm(ImportRoster $importRoster): void
    {
        $result = $importRoster->handle($this->course, $this->names, $this->withdraw);

        $this->startOver();
        $this->dispatch('roster-imported');

        Flux::modal('import-roster')->close();
        Flux::toast(variant: 'success', heading: __('Roster imported'), text: __(':added added, :withdrawn deleted.', $result));
    }

    public function removeRoster(): void
    {
        $this->roster?->delete();
        $this->roster = null;
    }

    public function startOver(): void
    {
        $this->removeRoster();
        $this->reset('names', 'withdraw');
        $this->resetValidation();
        unset($this->missingStudents);
    }
}; ?>

<flux:modal name="import-roster" class="w-full max-w-lg" wire:close="startOver">
    @if ($names === [])
        <form wire:submit="import" class="space-y-6">
            <flux:heading size="lg">{{ __('Import roster') }}</flux:heading>

            <flux:file-upload wire:model="roster" :label="__('Roster file')">
                <flux:file-upload.dropzone :heading="__('Drop a file here or click to browse')" :text="__('CSV or Excel file with a Name column of First, Last. Up to 1MB.')" inline />
            </flux:file-upload>

            @if ($roster)
                <flux:file-item :heading="$roster->getClientOriginalName()" :size="$roster->getSize()">
                    <x-slot name="actions">
                        <flux:file-item.remove wire:click="removeRoster" :aria-label="__('Remove file: :name', ['name' => $roster->getClientOriginalName()])" />
                    </x-slot>
                </flux:file-item>
            @endif

            <flux:callout icon="information-circle" :text="__('Students already in this class keep their preferred names and pronouns. You will be asked about anyone missing from the file.')" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">{{ __('Continue') }}</flux:button>
            </div>
        </form>
    @else
        <form wire:submit="confirm" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ trans_choice('{1} 1 student isn\'t in this roster|[2,*] :count students aren\'t in this roster', $this->missingStudents->count()) }}</flux:heading>
                <flux:text class="mt-2">{{ __('Check anyone who has left the class. They will be permanently deleted along with their testing history. Anyone left unchecked stays in the class.') }}</flux:text>
            </div>

            <flux:checkbox.group wire:model="withdraw" :label="__('Left the class')">
                @foreach ($this->missingStudents as $student)
                    <flux:checkbox :value="$student->id" :label="$student->display_name" wire:key="withdraw-{{ $student->id }}" />
                @endforeach
            </flux:checkbox.group>

            <div class="flex justify-end gap-2">
                <flux:button wire:click="startOver" variant="ghost">{{ __('Back') }}</flux:button>
                <flux:button type="submit" variant="primary">{{ __('Import') }}</flux:button>
            </div>
        </form>
    @endif
</flux:modal>
