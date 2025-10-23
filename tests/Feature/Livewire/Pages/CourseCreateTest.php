<?php

use App\Livewire\Pages\CourseCreate;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(CourseCreate::class)
        ->assertStatus(200);
});
