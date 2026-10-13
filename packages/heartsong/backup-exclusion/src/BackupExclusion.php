<?php

namespace Heartsong\BackupExclusion;

class BackupExclusion
{
    /**
     * Exclude the app's data from iCloud and device backups. Returns null outside the native app.
     */
    public function apply(): ?bool
    {
        return $this->call('BackupExclusion.Apply');
    }

    public function isExcluded(): ?bool
    {
        return $this->call('BackupExclusion.Status');
    }

    protected function call(string $method): ?bool
    {
        if (! function_exists('nativephp_call')) {
            return null;
        }

        $result = json_decode((string) nativephp_call($method, '{}'));

        return isset($result->data->excluded) ? (bool) $result->data->excluded : null;
    }
}
