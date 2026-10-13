<?php

use App\Enums\Day;
use App\Models\Attempt;
use App\Models\Course;
use App\Models\CourseMeeting;
use App\Models\Period;
use App\Models\Song;
use App\Models\Student;
use App\NativeComponents\Settings;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Testing\Native;

test('ending the school year deletes students and attempts only after confirming', function () {
    $course = Course::factory()->create();
    Attempt::factory()->for(Student::factory()->for($course))->for(Song::factory()->for($course))->create();

    $screen = Native::visit('/settings')
        ->assertScreen(Settings::class)
        ->tap('End school year')
        ->assertNativeCalled('Dialog.Alert');

    $screen->emitNative(ButtonPressed::class, ['index' => 0, 'label' => 'Cancel', 'id' => 'end-school-year']);
    expect(Attempt::count())->toBe(1);

    $screen->emitNative(ButtonPressed::class, ['index' => 1, 'label' => 'Delete everything', 'id' => 'end-school-year'])
        ->assertNativeCalled('Dialog.Toast');

    expect(Attempt::count())->toBe(0)
        ->and(Course::count())->toBe(1);
});

test('the bell schedule lists periods in order with their times', function () {
    Native::visit('/settings')
        ->assertSee('1st Period')
        ->assertSee('8:20 AM – 9:20 AM')
        ->assertSee('7th Period');
});

test('a period is added after the last one', function () {
    Native::test(Settings::class)
        ->tap('Add period')
        ->assertSet('startsAt', '16:15')
        ->assertSet('endsAt', '17:00')
        ->input('periodName', 'After school')
        ->call('savePeriod')
        ->assertSet('showPeriod', false);

    expect(Period::query()->ordered()->get()->last())
        ->name->toBe('After school')
        ->starts_at->toBe('16:15')
        ->ends_at->toBe('17:00');
});

test('editing a period changes when its classes meet', function () {
    $course = Course::factory()->create();
    $course->meetings()->create(['day' => Day::Monday, 'period_id' => period('6th Period')->id]);

    Native::test(Settings::class)
        ->call('editPeriod', period('6th Period')->id)
        ->set('startsAt', '13:15')
        ->set('endsAt', '14:05')
        ->call('savePeriod');

    expect(period('6th Period')->formattedTimes())->toBe('1:15-2:05')
        ->and($course->fresh()->meets)->toBe('6th Period Monday');
});

test('a period has to end after it starts and have a unique name', function () {
    Native::test(Settings::class)
        ->call('editPeriod', period('2nd Period')->id)
        ->set('periodName', '1st Period')
        ->set('endsAt', '09:00')
        ->call('savePeriod')
        ->assertSet('showPeriod', true)
        ->assertSee('The name has already been taken.')
        ->assertSee('The period has to end after it starts.');

    expect(period('2nd Period')->ends_at)->toBe('10:20');
});

test('periods move up and down the schedule', function () {
    Native::test(Settings::class)->call('movePeriod', period('2nd Period')->id, -1);

    expect(Period::query()->ordered()->limit(2)->pluck('name')->all())->toBe(['2nd Period', '1st Period']);
});

test('deleting a period removes its meeting times after confirming', function () {
    $course = Course::factory()->create();
    $course->meetings()->create(['day' => Day::Monday, 'period_id' => period('3rd Period')->id]);

    Native::test(Settings::class)
        ->call('confirmDeletePeriod', period('3rd Period')->id)
        ->assertNativeCalled('Dialog.Alert')
        ->emitNative(ButtonPressed::class, ['index' => 1, 'label' => 'Delete', 'id' => 'delete-period-'.period('3rd Period')->id]);

    expect(Period::where('name', '3rd Period')->exists())->toBeFalse()
        ->and(CourseMeeting::count())->toBe(0)
        ->and($course->fresh())->not->toBeNull();
});
