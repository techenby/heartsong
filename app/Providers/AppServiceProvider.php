<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Heartsong\BackupExclusion\Facades\BackupExclusion;
use Heartsong\DeviceTimezone\Facades\DeviceTimezone;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureDefaults();
        $this->useDeviceTimezone();

        // Student data must never leave the iPad, including through iCloud backups.
        BackupExclusion::apply();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );
    }

    protected function useDeviceTimezone(): void
    {
        $timezone = DeviceTimezone::get();

        if ($timezone === null) {
            return;
        }

        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
    }
}
