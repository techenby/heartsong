<?php

use App\Models\Attempt;
use App\Models\Course;
use App\Models\Song;
use App\Models\Student;

beforeEach(function () {
    actingAsTenant();
    $this->course = Course::factory()->create();
    $this->first = Song::factory()->for($this->course)->create(['position' => 0]);
    $this->second = Song::factory()->for($this->course)->create(['position' => 1]);
    $this->student = Student::factory()->for($this->course)->create();
});

test('the current song is the first song not yet passed', function () {
    Attempt::factory()->for($this->student)->for($this->first)->create(['passed' => false]);
    expect($this->student->load('attempts')->currentSong($this->course->songs)->id)->toBe($this->first->id);

    Attempt::factory()->for($this->student)->for($this->first)->create(['passed' => true]);
    expect($this->student->load('attempts')->currentSong($this->course->songs)->id)->toBe($this->second->id);
});

test('there is no current song once every song is passed', function () {
    Attempt::factory()->for($this->student)->for($this->first)->create(['passed' => true]);
    Attempt::factory()->for($this->student)->for($this->second)->create(['passed' => true]);

    expect($this->student->load('attempts')->currentSong($this->course->songs))->toBeNull();
});

test('tries are counted per song', function () {
    Attempt::factory()->for($this->student)->for($this->first)->count(2)->create();
    Attempt::factory()->for($this->student)->for($this->second)->create();

    expect($this->student->triesOn($this->first))->toBe(2)
        ->and($this->student->triesOn($this->second))->toBe(1);
});

test('only an attempt from today counts as tested today', function () {
    $this->freezeTime();
    Attempt::factory()->for($this->student)->for($this->first)->create(['attempted_on' => today()->subDay()]);

    expect($this->student->load('attempts')->testedToday())->toBeNull();

    $today = Attempt::factory()->for($this->student)->for($this->first)->create(['attempted_on' => today()]);

    expect($this->student->load('attempts')->testedToday()->id)->toBe($today->id);
});

test('students are put in testing order with unplaced students last', function () {
    $this->student->update(['last_name' => 'Zed', 'testing_position' => 1]);
    Student::factory()->for($this->course)->create(['last_name' => 'Young', 'testing_position' => 0]);
    Student::factory()->for($this->course)->create(['last_name' => 'Brown', 'testing_position' => null]);
    Student::factory()->for($this->course)->create(['last_name' => 'Adams', 'testing_position' => null]);

    expect($this->course->students()->inTestingOrder()->pluck('last_name')->all())
        ->toBe(['Young', 'Zed', 'Adams', 'Brown']);
});
