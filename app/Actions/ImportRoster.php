<?php

namespace App\Actions;

use App\Models\Course;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\SimpleExcel\SimpleExcelReader;

/**
 * Syncs a course's students with a roster spreadsheet (CSV or XLSX) whose "Name" column holds "First, Last".
 *
 * Students are matched by name so their preferred names and pronouns survive a reimport.
 * Students missing from the file are only deleted when the teacher confirms they left, and
 * then permanently, along with their testing history: Illinois only treats a teacher's notes
 * as personal records if they're destroyed once the student leaves.
 */
class ImportRoster
{
    /**
     * @param  list<array{0: string, 1: string}>  $names
     * @param  array<int, int|string>  $withdrawIds  Missing students the teacher confirmed have left.
     * @return array{added: int, withdrawn: int}
     */
    public function handle(Course $course, array $names, array $withdrawIds = []): array
    {
        return DB::transaction(function () use ($course, $names, $withdrawIds) {
            ['new' => $new, 'missing' => $missing] = $this->match($course, $names);

            foreach ($new as [$firstName, $lastName]) {
                $course->students()->create(['first_name' => $firstName, 'last_name' => $lastName]);
            }

            $withdrawn = $missing->whereIn('id', array_map('intval', $withdrawIds));
            $withdrawn->each->delete();

            return ['added' => count($new), 'withdrawn' => $withdrawn->count()];
        });
    }

    /**
     * @param  list<array{0: string, 1: string}>  $names
     * @return Collection<int, Student>
     */
    public function missingStudents(Course $course, array $names): Collection
    {
        return $this->match($course, $names)['missing'];
    }

    /**
     * @param  'csv'|'xlsx'  $type
     * @return list<array{0: string, 1: string}>
     *
     * @throws ValidationException
     */
    public function readNames(string $path, string $type = 'csv'): array
    {
        $reader = SimpleExcelReader::create($path, $type)
            ->formatHeadersUsing(fn ($header) => strtolower(trim((string) $header, " \t\n\r\0\x0B\u{FEFF}")));

        if (! in_array('name', $reader->getHeaders() ?? [], true)) {
            throw ValidationException::withMessages(['roster' => 'The roster needs a "Name" column.']);
        }

        $names = [];
        $unreadable = [];

        foreach ($reader->getRows() as $row) {
            $cell = is_scalar($row['name']) ? trim((string) $row['name']) : '';

            if ($cell === '') {
                continue;
            }

            [$firstName, $lastName] = array_map('trim', explode(',', $cell, 2)) + [1 => ''];

            if ($firstName === '' || $lastName === '') {
                $unreadable[] = $cell;

                continue;
            }

            $names[] = [$firstName, $lastName];
        }

        if ($unreadable !== []) {
            throw ValidationException::withMessages([
                'roster' => 'Names should look like "First, Last". Check: '.implode('; ', $unreadable),
            ]);
        }

        if ($names === []) {
            throw ValidationException::withMessages(['roster' => 'The roster has no students.']);
        }

        return $names;
    }

    /**
     * @param  list<array{0: string, 1: string}>  $names
     * @return array{new: list<array{0: string, 1: string}>, missing: Collection<int, Student>}
     */
    protected function match(Course $course, array $names): array
    {
        $groups = $course->students()->get()
            ->groupBy(fn (Student $student) => $this->key($student->first_name, $student->last_name));

        $new = [];

        foreach ($names as [$firstName, $lastName]) {
            $student = $groups->get($this->key($firstName, $lastName))?->shift();

            if (! $student) {
                $new[] = [$firstName, $lastName];
            }
        }

        $missing = $groups->flatten(1)->values();

        return ['new' => $new, 'missing' => $missing];
    }

    protected function key(string $firstName, string $lastName): string
    {
        return mb_strtolower("{$firstName}|{$lastName}");
    }
}
