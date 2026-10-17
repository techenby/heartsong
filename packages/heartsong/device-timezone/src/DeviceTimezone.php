<?php

namespace Heartsong\DeviceTimezone;

use DateTimeZone;
use Exception;

class DeviceTimezone
{
    /**
     * The device's timezone identifier, e.g. "America/Chicago". Returns null outside the native app,
     * or if the device reports a timezone PHP doesn't recognize.
     */
    public function get(): ?string
    {
        if (! function_exists('nativephp_call')) {
            return null;
        }

        $result = json_decode((string) nativephp_call('DeviceTimezone.Get', '{}'));
        $timezone = $result->data->timezone ?? null;

        if (! is_string($timezone)) {
            return null;
        }

        try {
            return (new DateTimeZone($timezone))->getName();
        } catch (Exception) {
            return null;
        }
    }
}
