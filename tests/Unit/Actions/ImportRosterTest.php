<?php

use App\Actions\ImportRoster;
use App\Models\Attempt;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->course = Course::factory()->create();
    $this->importRoster = app(ImportRoster::class);
});

function rosterNames(Course $course): array
{
    return $course->students()->orderBy('id')->get()
        ->map(fn (Student $student) => "{$student->first_name} {$student->last_name}")
        ->all();
}

test('names are read from the Name column', function () {
    $csv = "\u{FEFF}Student ID,NAME,Homeroom\n1,\"  Jane  ,  Doe  \",A101\n2,\"John, Smith\",A101\n\n3,,A101\n";

    expect($this->importRoster->readNames(rosterFile($csv)))->toBe([['Jane', 'Doe'], ['John', 'Smith']]);
});

test('names are read from an Excel roster', function () {
    $path = excelRoster([
        ['Student ID' => 1, 'Name' => 'Jane, Doe'],
        ['Student ID' => 2, 'Name' => ' John , Smith '],
    ]);

    expect($this->importRoster->readNames($path, 'xlsx'))->toBe([['Jane', 'Doe'], ['John', 'Smith']]);
});

test('unreadable rosters are rejected', function (string $csv, string $message) {
    expect(fn () => $this->importRoster->readNames(rosterFile($csv)))
        ->toThrow(ValidationException::class, $message);
})->with([
    'missing Name column' => ["Student\n\"Jane, Doe\"\n", 'The roster needs a "Name" column.'],
    'names without a comma' => ["Name\n\"Jane, Doe\"\nCher\n\"Prince,\"\n", 'Names should look like "First, Last". Check: Cher; Prince,'],
    'no students' => ["Name\n\n", 'The roster has no students.'],
]);

test('new students are added and matching students keep their details', function () {
    $kept = Student::factory()->for($this->course)->create([
        'first_name' => 'Robert', 'last_name' => 'Young', 'preferred_name' => 'Bobby', 'pronouns' => 'he/him',
    ]);

    $result = $this->importRoster->handle($this->course, [['robert', 'young'], ['Ada', 'Abbott']]);

    expect($result)->toBe(['added' => 1, 'withdrawn' => 0])
        ->and(rosterNames($this->course))->toBe(['Robert Young', 'Ada Abbott'])
        ->and($kept->fresh())
        ->preferred_name->toBe('Bobby')
        ->pronouns->toBe('he/him');
});

test('missing students are listed without being changed', function () {
    Student::factory()->for($this->course)->create(['first_name' => 'Ada', 'last_name' => 'Abbott']);
    $missing = Student::factory()->for($this->course)->create(['first_name' => 'Gone', 'last_name' => 'Away']);

    expect($this->importRoster->missingStudents($this->course, [['Ada', 'Abbott']])->pluck('id')->all())->toBe([$missing->id]);

    $this->importRoster->handle($this->course, [['Ada', 'Abbott']]);

    expect(rosterNames($this->course))->toBe(['Ada Abbott', 'Gone Away']);
});

test('missing students confirmed as having left are permanently deleted with their history', function () {
    $left = Student::factory()->for($this->course)->create(['first_name' => 'Gone', 'last_name' => 'Away']);
    $stays = Student::factory()->for($this->course)->create(['first_name' => 'Still', 'last_name' => 'Here']);

    Attempt::factory()->for($left)->create();

    $result = $this->importRoster->handle($this->course, [['Ada', 'Abbott']], [$left->id]);

    expect($result['withdrawn'])->toBe(1)
        ->and($left->fresh())->toBeNull()
        ->and(Attempt::count())->toBe(0)
        ->and($stays->fresh())->not->toBeNull();
});

test('only missing students can be withdrawn', function () {
    $onRoster = Student::factory()->for($this->course)->create(['first_name' => 'Ada', 'last_name' => 'Abbott']);
    $otherClass = Student::factory()->create();

    $result = $this->importRoster->handle($this->course, [['Ada', 'Abbott']], [$onRoster->id, $otherClass->id]);

    expect($result['withdrawn'])->toBe(0)
        ->and($onRoster->fresh())->not->toBeNull()
        ->and($otherClass->fresh())->not->toBeNull();
});

test('students who share a name are each kept on reimport', function () {
    $this->importRoster->handle($this->course, [['Sam', 'Lee'], ['Sam', 'Lee']]);
    $ids = $this->course->students()->pluck('id')->all();

    $result = $this->importRoster->handle($this->course, [['Sam', 'Lee'], ['Sam', 'Lee']]);

    expect($result['added'])->toBe(0)
        ->and($this->course->students()->pluck('id')->all())->toBe($ids)->toHaveCount(2);
});
