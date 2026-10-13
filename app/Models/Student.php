<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $course_id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $preferred_name
 * @property string|null $pronouns
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $testing_position
 * @property-read string $display_name
 * @property-read Course $course
 * @property-read Collection<int, Attempt> $attempts
 */
#[Fillable(['first_name', 'last_name', 'preferred_name', 'pronouns', 'testing_position'])]
class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return HasMany<Attempt, $this>
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    /**
     * The first of the course's songs this student hasn't passed, or null once they've passed them all.
     *
     * @param  Collection<int, Song>  $songs  The course's songs in order.
     */
    public function currentSong(Collection $songs): ?Song
    {
        $passedSongIds = $this->attempts->where('passed', true)->pluck('song_id');

        return $songs->first(fn (Song $song) => ! $passedSongIds->contains($song->id));
    }

    public function triesOn(Song $song): int
    {
        return $this->attempts->where('song_id', $song->id)->count();
    }

    public function testedToday(): ?Attempt
    {
        return $this->attempts->filter(fn (Attempt $attempt) => $attempt->attempted_on->isToday())->sortByDesc('id')->first();
    }

    /**
     * Students the teacher hasn't placed yet go after everyone else, alphabetically.
     *
     * @param  Builder<Student>  $query
     */
    #[Scope]
    protected function inTestingOrder(Builder $query): void
    {
        $query->orderByRaw('testing_position is null')
            ->orderBy('testing_position')
            ->orderBy('last_name')
            ->orderBy('first_name');
    }

    /**
     * The name to show teachers and classmates; the legal first name is only shown when editing.
     *
     * @return Attribute<non-falsy-string, never>
     */
    protected function displayName(): Attribute
    {
        return Attribute::get(fn (): string => ($this->preferred_name ?? $this->first_name).' '.$this->last_name);
    }
}
