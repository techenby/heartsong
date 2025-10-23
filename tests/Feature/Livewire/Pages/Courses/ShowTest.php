<?php

use App\Enum\Color;
use App\Enum\Grade;
use App\Livewire\Pages\Courses\Show;
use App\Models\Course;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->course = Course::factory()->create([
        'grade' => Grade::FIRST,
        'color' => Color::RED,
        'homeroom' => '123',
    ]);
});

test('page renders successfully', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('courses.show', $this->course))
        ->assertSeeLivewire(Show::class)
        ->assertSee('1st Grade')
        ->assertSee('bg-red-400')
        ->assertSee('123')
        ->assertStatus(200);
});

test('component renders successfully', function () {
    Livewire::test(Show::class, ['course' => $this->course])
        ->assertStatus(200)
        ->assertSee('1st Grade')
        ->assertSee('bg-red-400')
        ->assertSee('123');
});
