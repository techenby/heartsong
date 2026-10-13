<?php

use App\Models\Attempt;
use App\Models\Course;
use App\Models\Song;
use App\NativeComponents\Courses\Songs;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Testing\Native;

beforeEach(function () {
    $this->course = Course::factory()->create();
});

test('songs are added to the end of the list', function () {
    Song::factory()->for($this->course)->create(['name' => 'Hot Cross Buns', 'position' => 0]);

    Native::visit("/courses/{$this->course->id}/songs")
        ->input('name', '  Mary Had a Little Lamb ')
        ->tap('Add song')
        ->assertSet('name', '')
        ->assertSee('Mary Had a Little Lamb');

    expect(songOrder($this->course))->toBe(['Hot Cross Buns', 'Mary Had a Little Lamb']);
});

test('a song needs a name', function () {
    Native::test(Songs::class, params: ['course' => $this->course])
        ->call('addSong')
        ->assertSee('The song name field is required.');

    expect(Song::count())->toBe(0);
});

test('a song can be renamed', function () {
    $song = Song::factory()->for($this->course)->create();

    Native::test(Songs::class, params: ['course' => $this->course])
        ->call('editSong', $song->id)
        ->input('editingName', 'Ode to Joy')
        ->call('renameSong')
        ->assertSet('editingSongId', null);

    expect($song->fresh()->name)->toBe('Ode to Joy');
});

test('songs move up and down the list', function () {
    $first = Song::factory()->for($this->course)->create(['name' => 'First', 'position' => 0]);
    $second = Song::factory()->for($this->course)->create(['name' => 'Second', 'position' => 1]);

    $screen = Native::test(Songs::class, params: ['course' => $this->course])->call('moveSong', $first->id, 1);
    expect(songOrder($this->course))->toBe(['Second', 'First']);

    $screen->call('moveSong', $first->id, -1);
    expect(songOrder($this->course))->toBe(['First', 'Second']);

    $screen->call('moveSong', $first->id, -1);
    expect(songOrder($this->course))->toBe(['First', 'Second']);
});

test('deleting a song deletes its attempts after confirming', function () {
    $song = Song::factory()->for($this->course)->create();
    Attempt::factory()->for($song)->create();

    Native::test(Songs::class, params: ['course' => $this->course])
        ->call('confirmDelete', $song->id)
        ->assertNativeCalled('Dialog.Alert')
        ->emitNative(ButtonPressed::class, ['index' => 1, 'label' => 'Delete', 'id' => "delete-song-{$song->id}"]);

    expect(Song::count())->toBe(0)
        ->and(Attempt::count())->toBe(0);
});

test('songs from another class cannot be changed', function () {
    $otherSong = Song::factory()->create(['name' => 'Other']);

    expect(fn () => Native::test(Songs::class, params: ['course' => $this->course])->call('editSong', $otherSong->id))
        ->toThrow(ModelNotFoundException::class);
});
