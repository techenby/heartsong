<?php

namespace App\Models;

use App\Enums\Color;
use App\Enums\Day;
use App\Enums\Grade;
use App\Enums\Period;
use Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Grade $grade
 * @property Color $color
 * @property string $homeroom
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $formatted
 * @property-read string $meets
 * @property-read Collection<int, CourseMeeting> $meetings
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
        return $this->hasMany(CourseMeeting::class)->chaperone();
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
     * Summarize when the course meets, e.g. "1st Period, Monday & Thursday".
     *
     * @return Attribute<string, never>
     */
    protected function meets(): Attribute
    {
        return Attribute::get(function (): string {
            $days = $this->meetings->pluck('day')->unique()->sortBy(fn (Day $day) => array_search($day, Day::cases()))->values();
            $periods = $this->meetings->pluck('period')->unique()->sortBy(fn (Period $period) => array_search($period, Period::cases()))->values();

            if ($periods->count() === 1 && $days->count() > 1) {
                return $periods->first()->value.', '.$days->map->value->join(', ', ' & ');
            }

            if ($days->count() === 1 && $periods->count() > 1) {
                return $days->first()->value.', '.$periods->map->value->join(', ', ' & ');
            }

            return $this->meetings
                ->sortBy([
                    fn (CourseMeeting $a, CourseMeeting $b) => array_search($a->day, Day::cases()) <=> array_search($b->day, Day::cases()),
                    fn (CourseMeeting $a, CourseMeeting $b) => array_search($a->period, Period::cases()) <=> array_search($b->period, Period::cases()),
                ])
                ->map->formatted
                ->join(', ');
        });
    }
}
