<?php

use App\Jobs\ImportRoster;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('imports students from a CSV, replaces existing students, trims names, and deletes the file', function () {
    $course = Course::factory()->create();

    Student::factory()->for($course)->count(2)->createMany([
        ['first_name' => 'Existing', 'last_name' => 'Student'],
        ['first_name' => 'To', 'last_name' => 'Delete'],
    ]);

    $absolutePath = storage_path('app/private/imports/roster.csv');

    $handle = fopen($absolutePath, 'w');
    fputcsv($handle, ['Name']);
    fputcsv($handle, ['  Jane  ,  Doe  ']);
    fputcsv($handle, ['John,Smith']);
    fclose($handle);

    Storage::spy();

    new ImportRoster(course: $course, file: 'imports/roster.csv')->handle();

    expect($course->students)->toHaveCount(2)
        ->and($course->students)->sequence(
            fn ($studentA) => $studentA->first_name->toEqual('Jane') && $studentA->last_name->toEqual('Doe'),
            fn ($studentB) => $studentB->first_name->toEqual('John') && $studentB->last_name->toEqual('Smith'),
        );

    Storage::shouldHaveReceived('delete')->once()->with($absolutePath);
});
