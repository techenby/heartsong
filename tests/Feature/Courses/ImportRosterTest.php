<?php

use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\SimpleExcel\SimpleExcelWriter;

beforeEach(function () {
    actingAsTenant();
    Storage::fake('local');
    $this->course = Course::factory()->create();
});

function rosterUpload(string $contents): UploadedFile
{
    return UploadedFile::fake()->createWithContent('roster.csv', $contents);
}

test('a roster with nobody missing is imported straight away', function () {
    Livewire::test('courses.import-roster', ['course' => $this->course])
        ->set('roster', rosterUpload("Name\n\"Jane, Doe\"\n"))
        ->call('import')
        ->assertHasNoErrors()
        ->assertSet('names', [])
        ->assertDispatched('roster-imported');

    expect($this->course->students()->sole())
        ->first_name->toBe('Jane')
        ->last_name->toBe('Doe');
});

test('the teacher is asked about missing students before anything changes', function () {
    Student::factory()->for($this->course)->create(['first_name' => 'Gone', 'last_name' => 'Away']);
    $stays = Student::factory()->for($this->course)->create(['first_name' => 'Still', 'last_name' => 'Here']);

    $component = Livewire::test('courses.import-roster', ['course' => $this->course])
        ->set('roster', rosterUpload("Name\n\"Jane, Doe\"\n"))
        ->call('import')
        ->assertSee(['2 students aren&#039;t in this roster', 'Gone Away', 'Still Here'], escape: false)
        ->assertNotDispatched('roster-imported');

    expect($this->course->students()->count())->toBe(2);

    $left = $this->course->students()->firstWhere('first_name', 'Gone');

    $component->set('withdraw', [$left->id])
        ->call('confirm')
        ->assertDispatched('roster-imported');

    expect($left->fresh()->trashed())->toBeTrue()
        ->and($stays->fresh()->trashed())->toBeFalse()
        ->and($this->course->students()->pluck('first_name')->sort()->values()->all())->toBe(['Jane', 'Still']);
});

test('starting over discards the pending roster', function () {
    Student::factory()->for($this->course)->create(['first_name' => 'Gone', 'last_name' => 'Away']);

    Livewire::test('courses.import-roster', ['course' => $this->course])
        ->set('roster', rosterUpload("Name\n\"Jane, Doe\"\n"))
        ->call('import')
        ->call('startOver')
        ->assertSet('names', []);

    expect($this->course->students()->pluck('first_name')->all())->toBe(['Gone']);
});

test('an Excel roster can be uploaded', function () {
    $path = tempnam(sys_get_temp_dir(), 'roster').'.xlsx';
    SimpleExcelWriter::create($path)->addRow(['Name' => 'Jane, Doe'])->close();

    Livewire::test('courses.import-roster', ['course' => $this->course])
        ->set('roster', UploadedFile::fake()->createWithContent('roster.xlsx', file_get_contents($path)))
        ->call('import')
        ->assertHasNoErrors()
        ->assertDispatched('roster-imported');

    expect($this->course->students()->sole()->first_name)->toBe('Jane');
});

test('a roster file is required', function () {
    Livewire::test('courses.import-roster', ['course' => $this->course])
        ->call('import')
        ->assertHasErrors(['roster' => 'required']);
});

test('only CSV files are accepted', function () {
    Livewire::test('courses.import-roster', ['course' => $this->course])
        ->set('roster', UploadedFile::fake()->image('roster.png'))
        ->call('import')
        ->assertHasErrors(['roster' => 'mimes']);
});

test('problems in the roster are shown to the user', function () {
    Livewire::test('courses.import-roster', ['course' => $this->course])
        ->set('roster', rosterUpload("Student\n\"Jane, Doe\"\n"))
        ->call('import')
        ->assertHasErrors('roster')
        ->assertSee('The roster needs a &quot;Name&quot; column.', escape: false)
        ->assertNotDispatched('roster-imported');
});
