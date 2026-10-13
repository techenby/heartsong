<?php

namespace App\Models;

use App\Enums\Day;
use App\Enums\Period;
use Database\Factories\CourseMeetingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $course_id
 * @property Day $day
 * @property Period $period
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $formatted
 * @property-read Course $course
 */
#[Fillable(['day', 'period'])]
class CourseMeeting extends Model
{
    /** @use HasFactory<CourseMeetingFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day' => Day::class,
            'period' => Period::class,
        ];
    }

    /**
     * @return Attribute<string, never>
     */
    protected function formatted(): Attribute
    {
        return Attribute::get(fn (): string => "{$this->period->value} {$this->day->value}");
    }
}
