<?php

use App\Models\Attempt;
use App\Models\Course;
use App\Models\Song;
use Livewire\Livewire;

beforeEach(function () {
    $this->course = Course::factory()->create();
});

test('songs are added to the end of the list', function () {
    Song::factory()->for($this->course)->create(['name' => 'Hot Cross Buns', 'position' => 0]);

    Livewire::test('pages::courses.songs', ['course' => $this->course])
        ->set('name', '  Mary Had a Little Lamb ')
        ->call('addSong')
        ->assertHasNoErrors()
        ->assertSet('name', '')
        ->assertSeeInOrder(['Hot Cross Buns', 'Mary Had a Little Lamb']);

    expect($this->course->songs()->pluck('name')->all())->toBe(['Hot Cross Buns', 'Mary Had a Little Lamb']);
});

test('a song needs a name', function () {
    Livewire::test('pages::courses.songs', ['course' => $this->course])
        ->call('addSong')
        ->assertHasErrors(['name' => 'required']);
});

test('a song can be renamed', function () {
    $song = Song::factory()->for($this->course)->create();

    Livewire::test('pages::courses.songs', ['course' => $this->course])
        ->call('editSong', $song->id)
        ->set('editingName', 'Ode to Joy')
        ->call('renameSong')
        ->assertHasNoErrors();

    expect($song->fresh()->name)->toBe('Ode to Joy');
});

test('deleting a song deletes its attempts', function () {
    $song = Song::factory()->for($this->course)->create();
    Attempt::factory()->for($song)->create();

    Livewire::test('pages::courses.songs', ['course' => $this->course])->call('deleteSong', $song->id);

    expect(Song::count())->toBe(0)
        ->and(Attempt::count())->toBe(0);
});

test('songs can be reordered', function () {
    $first = Song::factory()->for($this->course)->create(['name' => 'First', 'position' => 0]);
    Song::factory()->for($this->course)->create(['name' => 'Second', 'position' => 1]);

    Livewire::test('pages::courses.songs', ['course' => $this->course])->call('moveSong', $first->id, 1);

    expect($this->course->songs()->pluck('name')->all())->toBe(['Second', 'First']);
});

test('songs from another class cannot be changed', function (string $method, array $arguments) {
    $otherSong = Song::factory()->create(['name' => 'Other']);

    Livewire::test('pages::courses.songs', ['course' => $this->course])
        ->call($method, ...array_map(fn ($argument) => $argument === 'song' ? $otherSong->id : $argument, $arguments))
        ->assertStatus(404);

    expect($otherSong->fresh()->name)->toBe('Other');
})->with([
    'rename' => ['editSong', ['song']],
    'delete' => ['deleteSong', ['song']],
    'move' => ['moveSong', ['song', 0]],
]);
