<?php

use App\Jobs\ImportRoster as ImportRosterJob;
use App\Livewire\Components\ImportRoster;
use App\Models\Course;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->course = Course::factory()->create();
});

it('renders successfully', function () {
    Livewire::test(ImportRoster::class, ['course' => $this->course])
        ->assertStatus(200);
});

it('stores the file and dispatches ImportRoster job on import', function () {
    Storage::fake();
    Bus::fake();

    Livewire::test(ImportRoster::class, ['course' => $this->course])
        ->set('roster', UploadedFile::fake()->create('roster.csv', 5))
        ->call('import');

    Bus::assertDispatched(ImportRosterJob::class, function (ImportRosterJob $job) {
        return $job->course->is($this->course) && str_starts_with($job->file, 'imports/');
    });
});

it('requires a roster file to import', function () {
    Livewire::test(ImportRoster::class, ['course' => $this->course])
        ->call('import')
        ->assertHasErrors(['roster' => 'required']);
});

it('validates the file must be a csv or txt', function () {
    Livewire::test(ImportRoster::class, ['course' => $this->course])
        ->set('roster', UploadedFile::fake()->create('roster.pdf', 10))
        ->call('import')
        ->assertHasErrors(['roster' => 'mimes']);
});

it('validates the file size must be <= 1024KB', function () {
    Livewire::test(ImportRoster::class, ['course' => $this->course])
        ->set('roster', UploadedFile::fake()->create('big.csv', 2000))
        ->call('import')
        ->assertHasErrors(['roster' => 'max']);
});

it('can remove an uploaded roster and reset state', function () {
    $file = UploadedFile::fake()->create('roster.csv', 5);

    Livewire::test(ImportRoster::class, ['course' => $this->course])
        ->set('roster', $file)
        ->call('remove')
        ->assertSet('roster', null);
});
