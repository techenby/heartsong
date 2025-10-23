<?php

namespace App\Jobs;

use App\Models\Course;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelReader;

class ImportRoster implements ShouldQueue
{
    use Queueable;

    public function __construct(public Course $course, public string $file)
    {}

    public function handle(): void
    {
        $path = storage_path('app/private/' . $this->file);
        $rows = SimpleExcelReader::create($path)->getRows();

        $this->course->students()->delete();

        $students = $rows->map(function(array $rowProperties) {
            [$firstName, $lastName] = explode(',', $rowProperties['Name']);

            return [
                'first_name' => trim($firstName),
                'last_name' => trim($lastName),
            ];
        });

        $this->course->students()->createMany($students);

        Storage::delete($path);
    }
}
