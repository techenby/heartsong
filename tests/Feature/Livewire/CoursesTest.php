<?php

use App\Livewire\Courses;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Courses::class)
        ->assertStatus(200);
});
