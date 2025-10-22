<?php

namespace App\Models;

use App\Enum\Day;
use App\Enum\Period;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseMeeting extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'day' => Day::class,
            'period' => Period::class,
        ];
    }
}
