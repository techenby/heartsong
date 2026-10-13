<?php

use App\Enums\Grade;
use App\Models\Course;
use Livewire\Livewire;

test('every class page loads', function (string $route) {
    $course = Course::factory()->create();

    $this->get(route($route, $course))->assertOk();
})->with(['courses.show', 'courses.edit', 'courses.songs', 'courses.testing']);

test('the classes and create pages load', function (string $route) {
    $this->get(route($route))->assertOk();
})->with(['courses.index', 'courses.create']);

test('classes are listed in grade order', function () {
    Course::factory()->create(['grade' => Grade::Second, 'homeroom' => 'B201']);
    Course::factory()->create(['grade' => Grade::Kindergarten, 'homeroom' => 'A101']);

    $this->get(route('courses.index', ['tab' => 'table']))
        ->assertOk()
        ->assertSeeInOrder(['Kindergarten', 'A101', '2nd Grade', 'B201']);
});

test('a class can be deleted', function () {
    $course = Course::factory()->create();

    Livewire::test('pages::courses.index')->call('delete', $course->id);

    expect(Course::count())->toBe(0);
});

test('deleting a missing class returns 404', function () {
    Livewire::test('pages::courses.index')
        ->call('delete', 999)
        ->assertStatus(404);
});
