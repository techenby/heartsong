<?php

use App\Enum\Color;
use App\Enum\Day;
use App\Enum\Grade;
use App\Enum\Period;
use App\Livewire\Pages\Courses\Edit;
use App\Models\Course;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->course = Course::factory()
        ->hasMeetings(1, ['day' => Day::MONDAY, 'period' => Period::FIRST])
        ->grade(Grade::FIRST)
        ->color(Color::RED)
        ->create(['homeroom' => 123]);
});

test('page renders successfully', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('courses.edit', $this->course))
        ->assertSeeLivewire(Edit::class)
        ->assertStatus(200);
});

test('component renders successfully', function () {
    Livewire::test(Edit::class, ['course' => $this->course])
        ->assertStatus(200);
});

test('can edit course', function () {
    Livewire::test(Edit::class, ['course' => $this->course])
        ->assertSet('grade', Grade::FIRST->name)
        ->assertSet('color', Color::RED->name)
        ->assertSet('homeroom', '123')
        ->assertSet('meetings.0.day', Day::MONDAY->name)
        ->assertSet('meetings.0.period', Period::FIRST->name)
        ->set('grade', Grade::SECOND->name)
        ->set('color', Color::ORANGE->name)
        ->set('homeroom', '456')
        ->set('meetings.0.day', Day::TUESDAY->name)
        ->set('meetings.0.period', Period::SECOND->name)
        ->call('save');

    $this->course->refresh();

    expect($this->course->grade)->toBe(Grade::SECOND)
        ->and($this->course->color)->toBe(Color::ORANGE)
        ->and($this->course->homeroom)->toBe('456');

    expect($this->course->meetings)->toHaveCount(1)
        ->and($this->course->meetings->first()->day)->toBe(Day::TUESDAY)
        ->and($this->course->meetings->first()->period)->toBe(Period::SECOND);
});

test('can add meeting', function () {
    Livewire::test(Edit::class, ['course' => $this->course])
        ->call('add')
        ->assertSeeHtmlInOrder([
            'key="meeting-0"',
            'key="meeting-1"',
        ])
        ->set('meetings.1.day', Day::THURSDAY->name)
        ->set('meetings.1.period', Period::FIRST->name)
        ->call('save');

    expect([$first, $second] = $this->course->fresh()->meetings)->toHaveCount(2)
        ->and($first->day)->toBe(Day::MONDAY)
        ->and($first->period)->toBe(Period::FIRST)
        ->and($second->day)->toBe(Day::THURSDAY)
        ->and($second->period)->toBe(Period::FIRST);
});

test('can duplicate meeting', function () {
    Livewire::test(Edit::class, ['course' => $this->course])
        ->set('meetings.0.day', Day::MONDAY->name)
        ->set('meetings.0.period', Period::FIRST->name)
        ->call('duplicate', 0)
        ->assertSeeHtmlInOrder([
            'key="meeting-0"',
            'key="meeting-1"',
        ])
        ->assertSet('meetings.1.day', Day::MONDAY->name)
        ->assertSet('meetings.1.period', Period::FIRST->name);
});

test('can remove meeting', function () {
    $meetingToDelete = $this->course->meetings->first();

    Livewire::test(Edit::class, ['course' => $this->course])
        ->assertSet('meetings.0.id', $meetingToDelete->id)
        ->assertSet('meetings.0.day', Day::MONDAY->name)
        ->assertSet('meetings.0.period', Period::FIRST->name)
        ->set('meetings.1.day', Day::TUESDAY->name)
        ->set('meetings.1.period', Period::SECOND->name)
        ->call('remove', 0)
        ->assertSet('meetings.0.day', Day::TUESDAY->name)
        ->assertSet('meetings.0.period', Period::SECOND->name);

    expect($meetingToDelete->fresh())->toBeNull();
})->note('When a meeting is removed, if the meeting exists it is deleted, the index is removed and values reset');

test('cannot delete last meeting', function () {
    Livewire::test(Edit::class, ['course' => $this->course])
        ->set('meetings.0.day', Day::MONDAY->name)
        ->set('meetings.0.period', Period::FIRST->name)
        ->call('remove', 0)
        ->assertSet('meetings.0.day', Day::MONDAY->name)
        ->assertSet('meetings.0.period', Period::FIRST->name);
})->note('All courses should have at least one meeting');
