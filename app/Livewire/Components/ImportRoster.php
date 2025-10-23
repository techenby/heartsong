<?php

namespace App\Livewire\Components;

use App\Jobs\ImportRoster as ImportRosterJob;
use App\Models\Course;
use Flux\Flux;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportRoster extends Component
{
    use WithFileUploads;

    public Course $course;

    #[Validate('required|file|mimes:csv,txt|max:1024')]
    public $roster;

    public function import(): void
    {
        $this->validate();

        $file = $this->roster->store(path: 'imports');

        ImportRosterJob::dispatch($this->course, $file);

        Flux::modal('import')->close();
    }

    public function remove(): void
    {
        $this->roster->delete();
        $this->roster = null;
    }
}
