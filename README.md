# JowXIdea OneSignal

A Laravel package for sending OneSignal push notifications with a simple client API, facade, and helper function.

## Features

- Laravel auto-discovery service provider
- Config publishing (`onesignal-config`)
- Container binding (`onesignal`)
- Facade (`OneSignal`) and helper (`onesignal()`)
- Send to all users, a single user, or multiple users
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
ONESIGNAL_USER_AUTH_KEY=optional_user_auth_key
ONESIGNAL_API_URL=https://onesignal.com/api/v1
ONESIGNAL_TIMEOUT=10
```

## Usage

### Send to all users

```php
onesignal()->sendToAll(
    ['en' => 'Hello everyone'],
    ['en' => 'Announcement'],
    ['source' => 'app']
);
```

### Send to one user

```php
onesignal()->sendToUser(
    'player-id-123',
    ['en' => 'Hello user']
);
```

### Send to many users

```php
onesignal()->sendToUsers(
    ['player-id-1', 'player-id-2'],
    ['en' => 'Hello group'],
    ['en' => 'Greeting'],
    ['source' => 'app']
);
```

### Using the facade

```php
use Jowxidea\Onesignal\Facades\OneSignal;

OneSignal::sendToSegments(
    ['Subscribed Users'],
    ['en' => 'New update is available'],
    ['en' => 'Update']
);
```

### Sending a custom payload

```php
onesignal()->send([
    'included_segments' => ['All'],
    'contents' => ['en' => 'Custom payload message'],
]);
```

### Legacy compatible methods

The client keeps compatibility methods such as `sendNotificationToAll`, `sendNotificationToUser`, `sendNotificationToExternalUser`, and `sendNotificationUsingTags` while using Laravel HTTP client internally.

## Testing

```bash
composer test
```

## License

MIT
