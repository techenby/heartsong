<?php

namespace App\Models;

use App\Enums\Day;
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
 * @property int $period_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $formatted
 * @property-read Course $course
 * @property-read Period $period
 */
#[Fillable(['day', 'period_id'])]
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
     * @return BelongsTo<Period, $this>
     */
    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'day' => Day::class,
        ];
    }

    /**
     * @return Attribute<non-falsy-string, never>
     */
    protected function formatted(): Attribute
    {
        return Attribute::get(fn (): string => "{$this->period->name} {$this->day->value}");
    }
}
