<?php

use App\Models\Tenant;
use App\Models\User;

test('deleting a user deletes their tenant and database', function () {
    $user = User::factory()->create();
    $database = database_path($user->provisionTenant()->database()->getName());

    $user->delete();

    expect(Tenant::count())->toBe(0)
        ->and($database)->not->toBeFile();
});
