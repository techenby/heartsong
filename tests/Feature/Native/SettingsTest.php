<?php

use App\Models\Attempt;
use App\Models\Course;
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
