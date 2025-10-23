<?php

use App\Livewire\Pages\CourseCreate;
use App\Models\User;
use Livewire\Livewire;

test('page renders successfully', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('courses.create'))
        ->assertSeeLivewire(CourseCreate::class)
        ->assertStatus(200);
});

test('component renders successfully', function () {
    Livewire::test(CourseCreate::class)
        ->assertStatus(200);
});
