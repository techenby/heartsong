<?php

namespace App\Models;

use App\Enums\Color;
use App\Enums\Day;
use App\Enums\Grade;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;

/**
 * @property int $id
 * @property Grade $grade
 * @property Color $color
 * @property string $homeroom
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $formatted
 * @property-read string $name
 * @property-read string $meets
 * @property-read Collection<int, CourseMeeting> $meetings
 * @property-read Collection<int, Song> $songs
 * @property-read Collection<int, Student> $students
 */
#[Fillable(['grade', 'color', 'homeroom'])]
class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    /**
     * @return HasMany<CourseMeeting, $this>
     */
    public function meetings(): HasMany
    {
        return $this->hasMany(CourseMeeting::class)->with('period')->chaperone();
    }

    /**
     * @return HasMany<Song, $this>
     */
    public function songs(): HasMany
    {
        return $this->hasMany(Song::class)->orderBy('position')->orderBy('id');
    }

    /**
     * @return HasMany<Student, $this>
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'grade' => Grade::class,
            'color' => Color::class,
        ];
    }

    /**
     * @return Attribute<non-falsy-string, never>
     */
    protected function formatted(): Attribute
    {
        return Attribute::get(fn (): string => "{$this->grade->short()} - {$this->homeroom}");
    }

    /**
     * The full label, e.g. "Kindergarten - A101".
     *
     * @return Attribute<non-falsy-string, never>
     */
    protected function name(): Attribute
    {
        return Attribute::get(fn (): string => "{$this->grade->value} - {$this->homeroom}");
    }

    /**
     * @return SupportCollection<int, CourseMeeting>
     */
    public function meetingsInWeekOrder(): SupportCollection
    {
        return $this->meetings->sortBy(fn (CourseMeeting $meeting) => [$meeting->day->ordinal(), $meeting->period->position])->values();
    }

    /**
     * Summarize when the course meets, e.g. "1st Period, Monday & Thursday".
     *
     * @return Attribute<string, never>
     */
    protected function meets(): Attribute
    {
        return Attribute::get(function (): string {
            $days = $this->meetings->pluck('day')->unique()->sortBy(fn (Day $day) => $day->ordinal())->values();
            $periods = $this->meetings->pluck('period')->unique('id')->sortBy('position')->values();

            if ($periods->count() === 1 && $days->count() > 1) {
                return $periods->first()->name.', '.$days->map->value->join(', ', ' & ');
            }

            if ($days->count() === 1 && $periods->count() > 1) {
                return $days->first()->value.', '.$periods->pluck('name')->join(', ', ' & ');
            }

            return $this->meetingsInWeekOrder()->map->formatted->join(', ');
        });
    }
}
