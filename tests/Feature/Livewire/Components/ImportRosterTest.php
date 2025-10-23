<?php

use App\Livewire\Components\ImportRoster;
use Livewire\Livewire;

test('renders successfully', function () {
    Livewire::test(ImportRoster::class)
        ->assertStatus(200);
});
