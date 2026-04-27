# JowXIdea OneSignal

A Laravel package for sending OneSignal push notifications with a simple client API, facade, and helper function.

## Features

- Laravel auto-discovery service provider
- Config publishing (`onesignal-config`)
- Container binding (`onesignal`)
- Facade (`OneSignal`) and helper (`onesignal()`)
- Testbench-based package tests

## Requirements

- PHP 8.1+
- Laravel 10/11/12

## Installation

```bash
composer require jowxidea/onesignal
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=onesignal-config
```

Set your environment variables:

```env
ONESIGNAL_APP_ID=your_app_id
ONESIGNAL_REST_API_KEY=your_rest_api_key
ONESIGNAL_API_URL=https://onesignal.com/api/v1
```

## Usage

### Using the helper

```php
$response = onesignal()->sendToUsers(
    ['player-id-1'],
    ['en' => 'Hello from Laravel package'],
    ['en' => 'Greeting'],
    ['source' => 'app']
);
```

### Using the facade

```php
use Jowxidea\Onesignal\Facades\OneSignal;

$response = OneSignal::sendToSegments(
    ['Subscribed Users'],
    ['en' => 'New update is available'],
    ['en' => 'Update']
);
```

### Sending a custom payload

```php
$response = onesignal()->send([
    'included_segments' => ['All'],
    'contents' => ['en' => 'Custom payload message'],
]);
```

## Testing

```bash
composer test
```

## License

MIT
