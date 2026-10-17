<?php

namespace Heartsong\DeviceTimezone\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string|null get()
 *
 * @see \Heartsong\DeviceTimezone\DeviceTimezone
 */
class DeviceTimezone extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Heartsong\DeviceTimezone\DeviceTimezone::class;
    }
}
