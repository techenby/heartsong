<?php

use App\Providers\AppServiceProvider;
use Heartsong\DeviceTimezone\Facades\DeviceTimezone;

beforeEach(function () {
    // 1 AM UTC on Oct 13 is still the evening of Oct 12 in Chicago.
    $this->travelTo('2026-10-13 01:00:00 UTC');
});

test('today follows the device timezone', function () {
    DeviceTimezone::shouldReceive('get')->andReturn('America/Chicago');

    app()->getProvider(AppServiceProvider::class)->boot();

    expect(config('app.timezone'))->toBe('America/Chicago')
        ->and(today()->toDateString())->toBe('2026-10-12');
});

test('today stays in UTC when the device timezone is unavailable', function () {
    DeviceTimezone::shouldReceive('get')->andReturnNull();

    app()->getProvider(AppServiceProvider::class)->boot();

    expect(config('app.timezone'))->toBe('UTC')
        ->and(today()->toDateString())->toBe('2026-10-13');
});
