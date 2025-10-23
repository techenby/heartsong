<?php

namespace App\Livewire\Pages\Courses;

use App\Models\Course;
use App\WithSorting;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    use WithSorting;

    #[Url]
    public string $sortBy = 'grade';

    #[Url]
    public string $sortDirection = 'desc';

    #[Url]
    public string $tab = 'calendar';

    public function render()
    {
        return view('livewire.pages.courses.index');
    }

    #[Computed]
    public function courses(): Collection
    {
        return Course::query()
            ->with('meetings')
            ->withCount('students')
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->get();
    }

    #[Computed]
    public function meetings(): Collection
    {
        return $this->courses->flatMap->meetings;
    }
}
