# Backup Exclusion Plugin for NativePHP Mobile

Keeps a NativePHP app's data out of iCloud and device backups by marking the app's
Application Support directory with `isExcludedFromBackup`. That directory holds the
SQLite database, Laravel storage (including uploads), and logs, so the data only ever
exists on the device. If the device is lost or reset, the data is gone.

iOS only.

## Installation

Add the path repository to the app's `composer.json`, then:

```bash
composer require heartsong/backup-exclusion
php artisan native:plugin:register heartsong/backup-exclusion
```

## Usage

```php
use Heartsong\BackupExclusion\Facades\BackupExclusion;

BackupExclusion::apply();      // true once excluded; null outside the native app
BackupExclusion::isExcluded(); // true, false, or null outside the native app
```

The flag can be cleared when iOS restores an app, so call `apply()` every time the app
starts rather than once at install.

## Events

None.

## License

MIT
