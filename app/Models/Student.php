<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
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
 * @property Carbon|null $deleted_at
 * @property-read string $display_name
 * @property-read Course $course
 */
#[Fillable(['first_name', 'last_name', 'preferred_name', 'pronouns'])]
class Student extends Model
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
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
