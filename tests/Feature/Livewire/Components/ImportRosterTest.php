<?php

use App\Livewire\Components\ImportRoster;
use Livewire\Livewire;

it('renders successfully', function () {
    Livewire::test(ImportRoster::class)
        ->assertStatus(200);
});
