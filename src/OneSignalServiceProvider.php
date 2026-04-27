<?php

declare(strict_types=1);

namespace Jowxidea\Onesignal;

use Illuminate\Http\Client\Factory;
use Illuminate\Support\ServiceProvider;

class OneSignalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/onesingle.php', 'onesingle');

        $this->app->singleton(OneSignalClient::class, function ($app): OneSignalClient {
            $config = (array) $app['config']->get('onesingle', []);

            return new OneSignalClient(
                (string) ($config['app_id'] ?? ''),
                (string) ($config['rest_api_key'] ?? ''),
                (string) ($config['api_url'] ?? 'https://onesignal.com/api/v1'),
                $app->make(Factory::class),
                (string) ($config['user_auth_key'] ?? ''),
                (int) ($config['timeout'] ?? 10)
            );
        });

        $this->app->alias(OneSignalClient::class, 'onesignal');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/onesingle.php' => config_path('onesingle.php'),
        ], 'onesignal-config');
    }
}
