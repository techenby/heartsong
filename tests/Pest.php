<?php

use App\Models\Course;
use App\Models\Period;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

/**
 * A period from the default bell schedule the migrations seed, e.g. period('1st Period').
 */
function period(string $name): Period
{
    return Period::query()->where('name', $name)->firstOrFail();
}

/**
 * Write roster file contents to a temporary file, as the document picker hands them over.
 */
function rosterFile(string $contents, string $extension = 'csv'): string
{
    $path = tempnam(sys_get_temp_dir(), 'roster').'.'.$extension;
    file_put_contents($path, $contents);

    return $path;
}

/**
 * Write an Excel roster to a temporary file.
 *
 * @param  array<int, array<string, mixed>>  $rows
 */
function excelRoster(array $rows): string
{
    $path = tempnam(sys_get_temp_dir(), 'roster').'.xlsx';
    $writer = SimpleExcelWriter::create($path);

    foreach ($rows as $row) {
        $writer->addRow($row);
    }

    $writer->close();

    return $path;
}

/**
 * @return array<int, string>
 */
function songOrder(Course $course): array
{
    return $course->songs()->pluck('name')->all();
}
