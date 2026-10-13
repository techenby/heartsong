<?php

use App\Models\User;

test('a private database is created for the user on their first visit', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))->assertOk();

    $tenant = $user->fresh()->tenant;

    expect($tenant)->not->toBeNull()
        ->and(tenant('id'))->toBe($tenant->id)
        ->and(database_path($tenant->database()->getName()))->toBeFile();
});

test('returning users keep the same tenant', function () {
    $user = User::factory()->create();
    $tenant = $user->provisionTenant();

    $this->actingAs($user)->get(route('dashboard'))->assertOk();

    expect($user->tenant()->count())->toBe(1)
        ->and(tenant('id'))->toBe($tenant->id);
});

test('each user gets a separate database', function () {
    $first = User::factory()->create()->provisionTenant();
    $second = User::factory()->create()->provisionTenant();

    expect($first->database()->getName())->not->toBe($second->database()->getName());
});

test('queued jobs stay in the central database while tenancy is active', function () {
    config(['queue.default' => 'database']);
    tenancy()->initialize(User::factory()->create()->provisionTenant());

    dispatch(fn () => null);

    $this->assertDatabaseCount('jobs', 1, 'sqlite');
});
