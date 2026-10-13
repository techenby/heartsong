<?php

use App\Enums\Color;
use App\Enums\Day;
use App\Enums\Grade;
use App\Enums\Period;
use App\Models\Course;
use App\Models\CourseMeeting;
use App\NativeComponents\Courses\Form;
use Native\Mobile\Testing\Native;

test('a class is created with its meeting times', function () {
    $screen = Native::visit('/courses/create')
        ->assertNavTitle('Create class')
        ->select('grade', Grade::Third->value)
        ->call('setColor', Color::Blue->value)
        ->input('homeroom', 'B204')
        ->set('meetings.0.day', Day::Tuesday->value)
        ->set('meetings.0.period', Period::First->value)
        ->call('addMeeting')
        ->set('meetings.1.day', Day::Thursday->value)
        ->set('meetings.1.period', Period::First->value)
        ->call('save');

    $course = Course::sole();

    $screen->assertReplacedWith("/courses/{$course->id}");
    expect($course->grade)->toBe(Grade::Third)
        ->and($course->color)->toBe(Color::Blue)
        ->and($course->homeroom)->toBe('B204')
        ->and($course->meets)->toBe('1st Period, Tuesday & Thursday');
});

test('missing fields are shown and nothing is saved', function () {
    Native::test(Form::class)
        ->call('save')
        ->assertNoNavigation()
        ->assertSee('The grade field is required.')
        ->assertSee('Choose a day.')
        ->assertSee('Choose a period.');

    expect(Course::count())->toBe(0);
});

test('the same time cannot be listed twice', function () {
    Native::test(Form::class)
        ->set('grade', Grade::First->value)
        ->set('color', Color::Red->value)
        ->set('homeroom', 'A104')
        ->set('meetings.0', ['day' => Day::Monday->value, 'period' => Period::Second->value])
        ->call('duplicateMeeting', 0)
        ->call('save')
        ->assertSee('This time is already listed.');

    expect(Course::count())->toBe(0);
});

test('the last meeting time cannot be removed', function () {
    Native::test(Form::class)
        ->call('removeMeeting', 0)
        ->assertSet('meetings', [['day' => '', 'period' => '']]);
});

test('editing a class fills the form and replaces its meeting times', function () {
    $course = Course::factory()->create(['grade' => Grade::Fourth, 'color' => Color::Purple, 'homeroom' => 'C301']);
    $course->meetings()->createMany([
        ['day' => Day::Monday, 'period' => Period::First],
        ['day' => Day::Tuesday, 'period' => Period::Second],
    ]);

    Native::visit("/courses/{$course->id}/edit")
        ->assertNavTitle('Edit class')
        ->assertSet('homeroom', 'C301')
        ->assertSet('meetings', [
            ['day' => 'Monday', 'period' => '1st Period'],
            ['day' => 'Tuesday', 'period' => '2nd Period'],
        ])
        ->set('meetings', [
            ['day' => 'Tuesday', 'period' => '2nd Period'],
            ['day' => 'Monday', 'period' => '1st Period'],
        ])
        ->input('homeroom', 'C302')
        ->call('save')
        ->assertWentBack();

    expect($course->fresh()->homeroom)->toBe('C302')
        ->and(CourseMeeting::count())->toBe(2);
});
