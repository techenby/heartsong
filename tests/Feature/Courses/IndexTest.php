<?php

use App\Enums\Grade;
use App\Models\Course;
use Livewire\Livewire;

test('guests are redirected to the login page', function (string $route) {
    $this->get($route)->assertRedirect(route('login'));
})->with([
    'index' => fn () => route('courses.index'),
    'create' => fn () => route('courses.create'),
    'show' => fn () => route('courses.show', 1),
    'edit' => fn () => route('courses.edit', 1),
]);

test('classes are listed in grade order', function () {
    $user = actingAsTenant();
    Course::factory()->create(['grade' => Grade::Second, 'homeroom' => 'B201']);
    Course::factory()->create(['grade' => Grade::Kindergarten, 'homeroom' => 'A101']);
    tenancy()->end();

    $this->actingAs($user)
        ->get(route('courses.index', ['tab' => 'table']))
        ->assertOk()
        ->assertSeeInOrder(['Kindergarten', 'A101', '2nd Grade', 'B201']);
});

test('a class can be deleted', function () {
    actingAsTenant();
    $course = Course::factory()->create();

    Livewire::test('pages::courses.index')->call('delete', $course->id);

    expect(Course::count())->toBe(0);
});

test('a class from another user cannot be deleted', function () {
    $owner = actingAsTenant();
    $course = Course::factory()->create();

    actingAsTenant();

    Livewire::test('pages::courses.index')
        ->call('delete', $course->id)
        ->assertStatus(404);

    tenancy()->initialize($owner->tenant);
    expect(Course::count())->toBe(1);
});
