<?php

namespace App\Models;

use Database\Factories\PeriodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A slot in the school's bell schedule, e.g. "1st Period" from 08:20 to 09:20.
 *
 * @property int $id
 * @property string $name
 * @property string $starts_at "H:i"
 * @property string $ends_at "H:i"
 * @property int $position
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'starts_at', 'ends_at', 'position'])]
class Period extends Model
{
    /** @use HasFactory<PeriodFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (Period $period): void {
            $period->position ??= (static::max('position') ?? -1) + 1;
        });
    }

    /**
     * @return HasMany<CourseMeeting, $this>
     */
    public function meetings(): HasMany
    {
        return $this->hasMany(CourseMeeting::class);
    }

    /**
     * "8:20-9:20" for compact grids, or "8:20 AM – 9:20 AM" with the meridiem.
     */
    public function formattedTimes(bool $withMeridiem = false): string
    {
        $format = $withMeridiem ? 'g:i A' : 'g:i';
        $separator = $withMeridiem ? ' – ' : '-';

        return Carbon::parse($this->starts_at)->format($format).$separator.Carbon::parse($this->ends_at)->format($format);
    }

    /**
     * @param  Builder<Period>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('position')->orderBy('starts_at');
    }
}
