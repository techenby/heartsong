<?php

namespace Heartsong\BackupExclusion\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool|null apply()
 * @method static bool|null isExcluded()
 *
 * @see \Heartsong\BackupExclusion\BackupExclusion
 */
class BackupExclusion extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Heartsong\BackupExclusion\BackupExclusion::class;
    }
}
