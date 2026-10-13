<?php

namespace Heartsong\BackupExclusion;

use Illuminate\Support\ServiceProvider;

class BackupExclusionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BackupExclusion::class);
    }
}
