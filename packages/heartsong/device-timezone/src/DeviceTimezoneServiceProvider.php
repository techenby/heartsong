<?php

namespace Heartsong\DeviceTimezone;

use Illuminate\Support\ServiceProvider;

class DeviceTimezoneServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DeviceTimezone::class);
    }
}
