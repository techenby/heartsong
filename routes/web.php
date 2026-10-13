<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/courses')->name('home');

Route::livewire('courses', 'pages::courses.index')->name('courses.index');
Route::livewire('courses/create', 'pages::courses.create')->name('courses.create');
Route::livewire('courses/{course}', 'pages::courses.show')->name('courses.show');
Route::livewire('courses/{course}/edit', 'pages::courses.edit')->name('courses.edit');
Route::livewire('courses/{course}/songs', 'pages::courses.songs')->name('courses.songs');
Route::livewire('courses/{course}/testing', 'pages::courses.testing')->name('courses.testing');

Route::livewire('settings/appearance', 'pages::settings.appearance')->name('appearance.edit');
