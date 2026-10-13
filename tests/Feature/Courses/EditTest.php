<?php

use App\Enums\Color;
use App\Enums\Day;
use App\Enums\Grade;
use App\Enums\Period;
use App\Models\Course;
use App\Models\CourseMeeting;
use Livewire\Livewire;

beforeEach(function () {
    $this->course = Course::factory()->create(['grade' => Grade::Fourth, 'color' => Color::Purple, 'homeroom' => 'C301']);
    $this->course->meetings()->createMany([
        ['day' => Day::Monday, 'period' => Period::First],
        ['day' => Day::Tuesday, 'period' => Period::Second],
    ]);
});

test('the form is filled with the class', function () {
    Livewire::test('pages::courses.edit', ['course' => $this->course])
        ->assertSet('form.grade', Grade::Fourth->value)
        ->assertSet('form.color', Color::Purple->value)
        ->assertSet('form.homeroom', 'C301')
        ->assertSet('form.meetings', [
            ['day' => 'Monday', 'period' => '1st Period'],
            ['day' => 'Tuesday', 'period' => '2nd Period'],
        ]);
});

test('a class and its meeting times are updated', function () {
    Livewire::test('pages::courses.edit', ['course' => $this->course])
        ->set('form.homeroom', 'C302')
        ->call('removeMeeting', 0)
        ->call('addMeeting')
        ->set('form.meetings.1', ['day' => Day::Friday->value, 'period' => Period::Second->value])
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirectToRoute('courses.show', $this->course);

    $course = $this->course->fresh();
    expect($course->homeroom)->toBe('C302')
        ->and($course->meets)->toBe('2nd Period, Tuesday & Friday');
});

test('two meeting times can swap slots', function () {
    Livewire::test('pages::courses.edit', ['course' => $this->course])
        ->set('form.meetings', [
            ['day' => 'Tuesday', 'period' => '2nd Period'],
            ['day' => 'Monday', 'period' => '1st Period'],
        ])
        ->call('save')
        ->assertHasNoErrors();

    expect(CourseMeeting::count())->toBe(2);
});

test('removing a meeting time is not saved until the form is submitted', function () {
    Livewire::test('pages::courses.edit', ['course' => $this->course])
        ->call('removeMeeting', 0);

    expect($this->course->meetings()->count())->toBe(2);
});
