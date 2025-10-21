<?php

use App\Livewire\CourseCreate;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(CourseCreate::class)
        ->assertStatus(200);
});
