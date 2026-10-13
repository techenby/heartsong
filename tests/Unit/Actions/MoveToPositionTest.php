<?php

use App\Actions\MoveToPosition;
use App\Models\Course;
use App\Models\Song;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function () {
    $this->course = Course::factory()->create();

    foreach (['A', 'B', 'C', 'D'] as $position => $name) {
        Song::factory()->for($this->course)->create(['name' => $name, 'position' => $position]);
    }
});

test('a model moves to its new position and the list is renumbered', function (string $name, int $position, array $expected) {
    $song = $this->course->songs()->firstWhere('name', $name);

    app(MoveToPosition::class)->handle($this->course->songs()->get(), $song->id, $position);

    expect(songOrder($this->course))->toBe($expected)
        ->and($this->course->songs()->pluck('position')->all())->toBe([0, 1, 2, 3]);
})->with([
    'down' => ['A', 2, ['B', 'C', 'A', 'D']],
    'up' => ['D', 0, ['D', 'A', 'B', 'C']],
    'past the end' => ['B', 10, ['A', 'C', 'D', 'B']],
]);

test('a model outside the list cannot be moved', function () {
    $otherSong = Song::factory()->create();

    expect(fn () => app(MoveToPosition::class)->handle($this->course->songs()->get(), $otherSong->id, 0))
        ->toThrow(ModelNotFoundException::class);
});

test('moving within an empty list is not found', function () {
    expect(fn () => app(MoveToPosition::class)->handle(new Collection, 1, 0))
        ->toThrow(ModelNotFoundException::class);
});
