<?php

use App\Livewire\Pages\CourseShow;
use App\Models\Course;
use App\Models\User;
use Livewire\Livewire;

test('page renders successfully', function () {
    $course = Course::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('courses.show', $course))
        ->assertSeeLivewire(CourseShow::class)
        ->assertStatus(200);
});


test('component renders successfully', function () {
    $course = Course::factory()->create();

    Livewire::test(CourseShow::class, ['course' => $course])
        ->assertStatus(200);
});
