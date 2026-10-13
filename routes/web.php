<?php

use App\Http\Middleware\InitializeTenancyForUser;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified', InitializeTenancyForUser::class])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('courses', 'pages::courses.index')->name('courses.index');
    Route::livewire('courses/create', 'pages::courses.create')->name('courses.create');
    Route::livewire('courses/{course}', 'pages::courses.show')->name('courses.show');
    Route::livewire('courses/{course}/edit', 'pages::courses.edit')->name('courses.edit');
    Route::livewire('courses/{course}/songs', 'pages::courses.songs')->name('courses.songs');
    Route::livewire('courses/{course}/testing', 'pages::courses.testing')->name('courses.testing');
});

require __DIR__.'/settings.php';
