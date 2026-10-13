<?php

use App\NativeComponents\Courses\Form;
use App\NativeComponents\Courses\Index;
use App\NativeComponents\Courses\Show;
use App\NativeComponents\Courses\Songs;
use App\NativeComponents\Courses\Testing;
use App\NativeComponents\Lock;
use App\NativeComponents\Settings;
use App\NativeLayouts\AppLayout;
use Illuminate\Support\Facades\Route;

Route::native('/', Lock::class);

Route::nativeGroup(AppLayout::class, function () {
    Route::native('/courses', Index::class);
    Route::native('/courses/create', Form::class);
    Route::native('/courses/{course}', Show::class);
    Route::native('/courses/{course}/edit', Form::class);
    Route::native('/courses/{course}/songs', Songs::class);
    Route::native('/courses/{course}/testing', Testing::class);
    Route::native('/settings', Settings::class);
});
