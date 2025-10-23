<?php

use App\Livewire\Pages\CourseShow;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(CourseShow::class)
        ->assertStatus(200);
});
