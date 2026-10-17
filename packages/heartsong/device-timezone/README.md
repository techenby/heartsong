# Device Timezone Plugin for NativePHP Mobile

PHP doesn't pick up the system timezone, so a NativePHP app runs in whatever
`app.timezone` says (UTC by default). This plugin reads `TimeZone.current` on the
device so the app can use local dates and times.

iOS only.

## Installation

Add the path repository to the app's `composer.json`, then:

```bash
composer require heartsong/device-timezone
php artisan native:plugin:register heartsong/device-timezone
```

## Usage

```php
use Heartsong\DeviceTimezone\Facades\DeviceTimezone;

DeviceTimezone::get(); // e.g. "America/Chicago"; null outside the native app
```

## Events

None.

## License

MIT
