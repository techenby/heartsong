<?php

use App\Enums\Color;
use App\Enums\Day;
use App\Enums\Grade;
use App\Enums\Period;
use App\Models\Course;
use App\NativeComponents\Courses\Index;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Testing\Native;

test('an empty app invites the teacher to create a class', function () {
    Native::visit('/courses')
        ->assertSee('No classes yet.')
        ->tap('Create class')
        ->assertNavigatedTo('/courses/create');
});

test('the calendar places each class in its time slot', function () {
    $course = Course::factory()->create(['grade' => Grade::Kindergarten, 'homeroom' => 'A101', 'color' => Color::Yellow]);
    $course->meetings()->create(['day' => Day::Monday, 'period' => Period::First]);

    Native::test(Index::class)
        ->assertSee('8:20-9:20')
        ->assertSee('K - A101')
        ->tap('K - A101')
        ->assertNavigatedTo("/courses/{$course->id}");
});

test('the list shows classes in grade order', function () {
    Course::factory()->create(['grade' => Grade::Second, 'homeroom' => 'B201']);
    Course::factory()->create(['grade' => Grade::Kindergarten, 'homeroom' => 'A101']);

    $screen = Native::test(Index::class)
        ->set('tab', 1)
        ->assertSee('Kindergarten - A101')
        ->assertSee('2nd Grade - B201');

    expect($screen->instance()->courses->pluck('homeroom')->all())->toBe(['A101', 'B201']);
});

test('a class is deleted only after confirming', function () {
    $course = Course::factory()->create();

    $screen = Native::test(Index::class)
        ->call('confirmDelete', $course->id)
        ->assertNativeCalled('Dialog.Alert');

    expect(Course::count())->toBe(1);

    $screen->emitNative(ButtonPressed::class, ['index' => 1, 'label' => 'Delete', 'id' => "delete-course-{$course->id}"]);

    expect(Course::count())->toBe(0);
});

test('cancelling the delete keeps the class', function () {
    $course = Course::factory()->create();

    Native::test(Index::class)
        ->call('confirmDelete', $course->id)
        ->emitNative(ButtonPressed::class, ['index' => 0, 'label' => 'Cancel', 'id' => "delete-course-{$course->id}"]);

    expect(Course::count())->toBe(1);
});
