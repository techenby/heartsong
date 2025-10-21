<?php

namespace App\Livewire;

use App\Models\Course;
use App\WithSorting;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class Courses extends Component
{
    use WithPagination;
    use WithSorting;

    #[Computed]
    public function courses(): LengthAwarePaginator
    {
        return Course::query()
            ->tap(fn ($query) => $this->sortBy ? $query->orderBy($this->sortBy, $this->sortDirection) : $query)
            ->paginate(10);
    }
}
