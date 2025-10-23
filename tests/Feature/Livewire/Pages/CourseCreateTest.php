<?php

use App\Enum\Color;
use App\Enum\Day;
use App\Enum\Grade;
use App\Enum\Period;
use App\Livewire\Pages\CourseCreate;
use App\Models\Course;
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

test('can create course', function () {
    Livewire::test(CourseCreate::class)
        ->set('grade', Grade::FIRST->name)
        ->set('color', Color::RED->name)
        ->set('homeroom', '123')
        ->set('meetings.0.day', Day::MONDAY->name)
        ->set('meetings.0.period', Period::FIRST->name)
        ->call('save');

    $course = Course::latest()->first();

    expect($course->grade)->toBe(Grade::FIRST)
        ->and($course->color)->toBe(Color::RED)
        ->and($course->homeroom)->toBe('123');

    expect($course->meetings)->toHaveCount(1)
        ->and($course->meetings->first()->day)->toBe(Day::MONDAY)
        ->and($course->meetings->first()->period)->toBe(Period::FIRST);
});

test('can add meeting', function () {
    Livewire::test(CourseCreate::class)
        ->call('add')
        ->assertSeeHtmlInOrder([
            'key="meeting-0"',
            'key="meeting-1"'
        ]);
});

test('can duplicate meeting', function () {
    Livewire::test(CourseCreate::class)
        ->set('meetings.0.day', Day::MONDAY->name)
        ->set('meetings.0.period', Period::FIRST->name)
        ->call('duplicate', 0)
        ->assertSeeHtmlInOrder([
            'key="meeting-0"',
            'key="meeting-1"'
        ])
        ->assertSet('meetings.1.day', Day::MONDAY->name)
        ->assertSet('meetings.1.period', Period::FIRST->name);
});

test('can remove meeting', function () {
    Livewire::test(CourseCreate::class)
        ->set('meetings.0.day', Day::MONDAY->name)
        ->set('meetings.0.period', Period::FIRST->name)
        ->set('meetings.1.day', Day::TUESDAY->name)
        ->set('meetings.1.period', Period::SECOND->name)
        ->set('meetings.2.day', Day::WEDNESDAY->name)
        ->set('meetings.2.period', Period::THIRD->name)
        ->call('remove', 1)
        ->assertSet('meetings.0.day', Day::MONDAY->name)
        ->assertSet('meetings.0.period', Period::FIRST->name)
        ->assertSet('meetings.1.day', Day::WEDNESDAY->name)
        ->assertSet('meetings.1.period', Period::THIRD->name);
})->note('When a meeting is removed, the index is removed and values reset');

test('cannot delete last meeting', function () {
    Livewire::test(CourseCreate::class)
        ->set('meetings.0.day', Day::MONDAY->name)
        ->set('meetings.0.period', Period::FIRST->name)
        ->call('remove', 0)
        ->assertSet('meetings.0.day', Day::MONDAY->name)
        ->assertSet('meetings.0.period', Period::FIRST->name);
})->note('All courses should have at least one meeting');
