<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('deleting a user deletes their tenant and database', function () {
    $user = User::factory()->create();
    $database = database_path($user->provisionTenant()->database()->getName());

    $user->delete();

    expect(Tenant::count())->toBe(0)
        ->and($database)->not->toBeFile();
});

test('deleting a user deletes their tenant storage folder', function () {
    $user = User::factory()->create();
    $tenant = $user->provisionTenant();
    $tenant->run(fn () => Storage::disk('local')->put('roster.csv', 'Name'));
    $folder = storage_path('tenant'.$tenant->getTenantKey());
    expect($folder)->toBeDirectory();

    $user->delete();

    expect($folder)->not->toBeDirectory();
});
