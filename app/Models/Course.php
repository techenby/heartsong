<?php

namespace App\Models;

use App\Enum\Day;
use App\Enum\Period;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function meetings(): HasMany
    {
        return $this->hasMany(CourseMeeting::class);
    }

    protected function meets(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->meetings->isEmpty()) {
                return '';
            }

            $uniqueDays = $this->meetings->pluck('day')->unique(fn (Day $d) => $d->value)->values();
            $uniquePeriods = $this->meetings->pluck('period')->unique(fn (Period $p) => $p->value)->values();

            // Same period, multiple days => "<period>, Monday & Thursday"
            if ($uniquePeriods->count() === 1 && $uniqueDays->count() > 1) {
                return $uniquePeriods->first()->value . ', ' . $uniqueDays->map->label()->join(', ', ' & ');
            }

            // Same day, multiple periods => "Monday, 1st period & 2nd period"
            if ($uniqueDays->count() === 1 && $uniquePeriods->count() > 1) {
                return $uniqueDays->first()->label() . ', ' . $uniquePeriods->map->value->join(', ', ' & ');
            }

            return $this->meetings->map->formatted->join(', ');
        });
    }
}
