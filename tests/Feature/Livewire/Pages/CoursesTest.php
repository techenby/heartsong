<?php

use App\Livewire\Pages\Courses;
use App\Models\User;
use Livewire\Livewire;

test('page renders successfully', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('courses'))
        ->assertSeeLivewire(Courses::class)
        ->assertStatus(200);
});

test('component renders successfully', function () {
    Livewire::test(Courses::class)
        ->assertStatus(200);
});
