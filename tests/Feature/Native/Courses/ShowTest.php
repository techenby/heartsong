<?php

use App\Models\Attempt;
use App\Models\Course;
use App\Models\Song;
use App\Models\Student;
use App\NativeComponents\Courses\Show;
use Native\Mobile\Events\Alert\ButtonPressed;
use Native\Mobile\Testing\Native;
use Smronju\NativephpDocumentPicker\Events\DocumentPicked;
use Spatie\SimpleExcel\SimpleExcelWriter;

function pickedRoster(string $contents, string $extension = 'csv'): string
{
    $path = tempnam(sys_get_temp_dir(), 'picked').'.'.$extension;
    file_put_contents($path, $contents);

    return $path;
}

test('the roster lists students by preferred name without their legal name', function () {
    $course = Course::factory()
        ->has(Student::factory()->state(['first_name' => 'Robert', 'preferred_name' => 'Bobby', 'last_name' => 'Young']))
        ->create();

    Native::visit("/courses/{$course->id}")
        ->assertScreen(Show::class)
        ->assertSee('Bobby Young')
        ->assertDontSee('Robert');
});

test('a student\'s preferred name and pronouns can be edited', function () {
    $student = Student::factory()->create(['first_name' => 'Robert']);

    Native::test(Show::class, params: ['course' => $student->course])
        ->call('editStudent', $student->id)
        ->assertSet('showDetails', true)
        ->input('preferredName', ' Bobby ')
        ->input('pronouns', 'he/him')
        ->call('saveStudent')
        ->assertSet('showDetails', false);

    expect($student->fresh())
        ->preferred_name->toBe('Bobby')
        ->pronouns->toBe('he/him');
});

test('student details have length limits', function () {
    $student = Student::factory()->create();

    Native::test(Show::class, params: ['course' => $student->course])
        ->call('editStudent', $student->id)
        ->set('preferredName', str_repeat('a', 101))
        ->call('saveStudent')
        ->assertSet('showDetails', true)
        ->assertSee('The preferred name field must not be greater than 100 characters.');
});

test('marking a student as left deletes them and their history after confirming', function () {
    $student = Student::factory()->create();
    Attempt::factory()->for($student)->for(Song::factory()->for($student->course))->create();

    Native::test(Show::class, params: ['course' => $student->course])
        ->call('confirmWithdraw', $student->id)
        ->assertNativeCalled('Dialog.Alert')
        ->emitNative(ButtonPressed::class, ['index' => 1, 'label' => 'Delete', 'id' => "withdraw-{$student->id}"]);

    expect($student->fresh())->toBeNull()
        ->and(Attempt::count())->toBe(0);
});

test('history lists attempts and mistakes can be deleted', function () {
    $student = Student::factory()->create();
    $song = Song::factory()->for($student->course)->create(['name' => 'Hot Cross Buns']);
    $attempt = Attempt::factory()->for($student)->for($song)->create(['notes' => 'Wrong student']);

    Native::test(Show::class, params: ['course' => $student->course])
        ->call('openHistory', $student->id)
        ->assertSee('Hot Cross Buns')
        ->call('deleteAttempt', $attempt->id)
        ->assertDontSee('Hot Cross Buns');

    expect(Attempt::count())->toBe(0);
});

test('choosing a roster opens the document picker for CSV and Excel files', function () {
    Native::test(Show::class, params: ['course' => Course::factory()->create()])
        ->call('openImport')
        ->tap('Choose file')
        ->assertNativeCalled('DocumentPicker.Pick');
});

test('a picked roster with nobody missing is imported and the temporary copy is deleted', function () {
    $course = Course::factory()->create();
    $path = pickedRoster("Name\n\"Jane, Doe\"\n");

    Native::test(Show::class, params: ['course' => $course])
        ->call('openImport')
        ->emitNative(DocumentPicked::class, ['path' => $path, 'name' => 'roster.csv'])
        ->assertSet('showImport', false)
        ->assertSee('Jane Doe')
        ->assertNativeCalled('Dialog.Toast');

    expect($path)->not->toBeFile();
});

test('the teacher confirms which missing students have left', function () {
    $course = Course::factory()->create();
    $left = Student::factory()->for($course)->create(['first_name' => 'Gone', 'last_name' => 'Away']);
    $stays = Student::factory()->for($course)->create(['first_name' => 'Still', 'last_name' => 'Here']);

    $screen = Native::test(Show::class, params: ['course' => $course])
        ->call('openImport')
        ->emitNative(DocumentPicked::class, ['path' => pickedRoster("Name\n\"Jane, Doe\"\n"), 'name' => 'roster.csv'])
        ->assertSee("2 students aren't in this roster");

    expect($course->students()->count())->toBe(2);

    $screen->call('toggleWithdraw', $left->id, true)->press('import');

    expect($left->fresh())->toBeNull()
        ->and($stays->fresh())->not->toBeNull();
});

test('an Excel roster can be imported', function () {
    $course = Course::factory()->create();
    $path = tempnam(sys_get_temp_dir(), 'picked').'.xlsx';
    SimpleExcelWriter::create($path)->addRow(['Name' => 'Jane, Doe'])->close();

    Native::test(Show::class, params: ['course' => $course])
        ->emitNative(DocumentPicked::class, ['path' => $path, 'name' => 'roster.xlsx']);

    expect($course->students()->sole()->first_name)->toBe('Jane');
});

test('problems in the roster are shown and nothing changes', function () {
    $course = Course::factory()->create();

    Native::test(Show::class, params: ['course' => $course])
        ->call('openImport')
        ->emitNative(DocumentPicked::class, ['path' => pickedRoster("Student\n\"Jane, Doe\"\n"), 'name' => 'roster.csv'])
        ->assertSee('The roster needs a "Name" column.');

    expect($course->students()->count())->toBe(0);
});

test('cancelling the picker changes nothing', function () {
    Native::test(Show::class, params: ['course' => Course::factory()->create()])
        ->call('openImport')
        ->emitNative(DocumentPicked::class, ['path' => null, 'name' => null])
        ->assertSet('showImport', true)
        ->assertSet('importNames', []);
});
