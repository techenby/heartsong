<?php

use App\Enums\Color;
use App\Enums\Day;
use App\Enums\Grade;
use App\Enums\Period;
use App\Models\Course;
use Livewire\Livewire;

beforeEach(function () {
    actingAsTenant();
});

test('a class is created with its meeting times', function () {
    $component = Livewire::test('pages::courses.create')
        ->set('form.grade', Grade::Third->value)
        ->set('form.color', Color::Blue->value)
        ->set('form.homeroom', 'B204')
        ->set('form.meetings.0', ['day' => Day::Tuesday->value, 'period' => Period::First->value])
        ->call('addMeeting')
        ->set('form.meetings.1', ['day' => Day::Thursday->value, 'period' => Period::First->value])
        ->call('save')
        ->assertHasNoErrors();

    $course = Course::sole();

    $component->assertRedirectToRoute('courses.show', $course);
    expect($course->grade)->toBe(Grade::Third)
        ->and($course->color)->toBe(Color::Blue)
        ->and($course->homeroom)->toBe('B204')
        ->and($course->meets)->toBe('1st Period, Tuesday & Thursday');
});

test('every field is required', function () {
    Livewire::test('pages::courses.create')
        ->call('save')
        ->assertHasErrors([
            'form.grade' => 'required',
            'form.color' => 'required',
            'form.homeroom' => 'required',
        ])
        ->assertSee(['Choose a day.', 'Choose a period.']);

    expect(Course::count())->toBe(0);
});

test('values outside the enums are rejected', function () {
    Livewire::test('pages::courses.create')
        ->set('form.grade', '12th Grade')
        ->set('form.color', 'Teal')
        ->set('form.meetings.0', ['day' => 'Saturday', 'period' => '9th Period'])
        ->call('save')
        ->assertHasErrors(['form.grade', 'form.color', 'form.meetings.0.day', 'form.meetings.0.period']);
});

test('the same time cannot be listed twice', function () {
    Livewire::test('pages::courses.create')
        ->set('form.grade', Grade::First->value)
        ->set('form.color', Color::Red->value)
        ->set('form.homeroom', 'A104')
        ->set('form.meetings.0', ['day' => Day::Monday->value, 'period' => Period::Second->value])
        ->call('duplicateMeeting', 0)
        ->call('save')
        ->assertHasErrors(['form.meetings.1.period'])
        ->assertHasNoErrors(['form.meetings.0.period'])
        ->assertSee('This time is already listed.');
});

test('the last meeting time cannot be removed', function () {
    Livewire::test('pages::courses.create')
        ->call('removeMeeting', 0)
        ->assertCount('form.meetings', 1);
});
