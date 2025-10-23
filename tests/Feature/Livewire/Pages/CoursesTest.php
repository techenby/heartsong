<?php

use App\Livewire\Pages\Courses;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(Courses::class)
        ->assertStatus(200);
});
