<?php

use App\Livewire\Pages\Courses\Create as CourseCreate;
use App\Livewire\Pages\Courses\Edit as CourseEdit;
use App\Livewire\Pages\Courses\Index as Courses;
use App\Livewire\Pages\Courses\Show as CourseShow;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::redirect('/', '/login')->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    Route::get('courses', Courses::class)->name('courses');
    Route::get('courses/create', CourseCreate::class)->name('courses.create');
    Route::get('courses/{course}', CourseShow::class)->name('courses.show');
    Route::get('courses/{course}/edit', CourseEdit::class)->name('courses.edit');
});
