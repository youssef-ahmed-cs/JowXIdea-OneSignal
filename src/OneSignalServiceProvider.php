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
                appId: (string) ($config['app_id'] ?? ''),
                restApiKey: (string) ($config['rest_api_key'] ?? ''),
                apiUrl: (string) ($config['api_url'] ?? 'https://onesignal.com/api/v1'),
                http: $app->make(Factory::class)
            );
        });

        $this->app->alias(OneSignalClient::class, 'onesignal');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');

        $this->publishes([
            __DIR__ . '/../config/onesingle.php' => config_path('onesingle.php'),
        ], 'onesignal-config');
    }
}
