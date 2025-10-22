<?php

namespace App\Livewire;

use App\Models\Course;
use App\WithSorting;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Courses extends Component
{
    use WithSorting;

    public string $sortBy = 'name';
    public string $sortDirection = 'desc';

    #[Computed]
    public function courses(): Collection
    {
        return Course::query()
            ->with('meetings')
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->get();
    }

    #[Computed]
    public function meetings(): Collection
    {
        return $this->courses->flatMap->meetings;
    }
}
