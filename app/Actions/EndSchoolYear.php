<?php

namespace App\Actions;

use App\Models\Attempt;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

/**
 * Permanently deletes every student and their testing history, keeping classes and songs so
 * next year's rosters can be imported into them. Illinois only treats a teacher's notes as
 * personal records if they're destroyed once students graduate or move on.
 */
class EndSchoolYear
{
    /**
     * @return array{students: int, attempts: int}
     */
    public function handle(): array
    {
        return DB::transaction(fn () => [
            'attempts' => Attempt::query()->delete(),
            'students' => Student::query()->delete(),
        ]);
    }
}
