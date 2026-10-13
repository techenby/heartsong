<?php

use App\Models\Attempt;
use App\Models\Course;
use App\Models\Song;
use App\Models\Student;
use Livewire\Livewire;

beforeEach(function () {
    actingAsTenant();
    $this->freezeTime();
    $this->course = Course::factory()->create();
    $this->firstSong = Song::factory()->for($this->course)->create(['name' => 'Hot Cross Buns', 'position' => 0]);
    $this->secondSong = Song::factory()->for($this->course)->create(['name' => 'Merrily We Roll Along', 'position' => 1]);
    $this->ada = Student::factory()->for($this->course)->create(['first_name' => 'Ada', 'testing_position' => 0]);
    $this->bea = Student::factory()->for($this->course)->create(['first_name' => 'Bea', 'testing_position' => 1]);
});

test('the first student in order who has not been tested today is up next', function () {
    Attempt::factory()->for($this->ada)->for($this->firstSong)->create(['attempted_on' => today()]);

    Livewire::test('pages::courses.testing', ['course' => $this->course])
        ->assertSet('selectedStudentId', null)
        ->assertSeeInOrder(['Up next', 'Bea']);
});

test('a pass is recorded on the student\'s current song and testing moves on', function () {
    Livewire::test('pages::courses.testing', ['course' => $this->course])
        ->set('notes', 'Great tone')
        ->call('record', true)
        ->assertSeeInOrder(['Up next', 'Bea']);

    expect($this->ada->attempts()->sole())
        ->song_id->toBe($this->firstSong->id)
        ->passed->toBeTrue()
        ->notes->toBe('Great tone')
        ->attempted_on->isToday()->toBeTrue();
});

test('the current song moves on once a student passes', function () {
    Attempt::factory()->for($this->ada)->for($this->firstSong)->create(['passed' => true, 'attempted_on' => today()->subWeek()]);

    Livewire::test('pages::courses.testing', ['course' => $this->course])
        ->call('record', false);

    expect($this->ada->attempts()->latest('id')->first())
        ->song_id->toBe($this->secondSong->id)
        ->passed->toBeFalse();
});

test('a student can be tested out of order on any song', function () {
    Livewire::test('pages::courses.testing', ['course' => $this->course])
        ->call('selectStudent', $this->bea->id)
        ->set('songId', (string) $this->secondSong->id)
        ->call('record', true)
        ->assertSet('selectedStudentId', null);

    expect($this->bea->attempts()->sole()->song_id)->toBe($this->secondSong->id)
        ->and($this->ada->attempts()->count())->toBe(0);
});

test('previous tries on the song are shown', function () {
    Attempt::factory()->for($this->ada)->for($this->firstSong)->count(2)->create(['passed' => false, 'attempted_on' => today()->subWeek()]);

    Livewire::test('pages::courses.testing', ['course' => $this->course])
        ->assertSee('2 previous tries on Hot Cross Buns');
});

test('students can be reordered', function () {
    Livewire::test('pages::courses.testing', ['course' => $this->course])
        ->call('moveStudent', $this->bea->id, 0)
        ->assertSeeInOrder(['Up next', 'Bea']);

    expect($this->bea->fresh()->testing_position)->toBe(0)
        ->and($this->ada->fresh()->testing_position)->toBe(1);
});

test('songs and students from another class cannot be used', function () {
    $otherSong = Song::factory()->create();
    $otherStudent = Student::factory()->create();

    Livewire::test('pages::courses.testing', ['course' => $this->course])
        ->call('selectStudent', $otherStudent->id)
        ->assertStatus(404);

    Livewire::test('pages::courses.testing', ['course' => $this->course])
        ->set('songId', (string) $otherSong->id)
        ->call('record', true)
        ->assertStatus(404);

    expect(Attempt::count())->toBe(0);
});

test('a class without songs is asked to add some', function () {
    $this->course->songs()->delete();

    Livewire::test('pages::courses.testing', ['course' => $this->course])
        ->assertSee('Add songs to start testing');
});
