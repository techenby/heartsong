<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Heartsong\BackupExclusion\Facades\BackupExclusion;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureDefaults();

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
}
