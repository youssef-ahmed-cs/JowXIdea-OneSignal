<?php

declare(strict_types=1);

namespace Jowxidea\Onesignal\Tests;

use Illuminate\Support\Facades\Route;
use Jowxidea\Onesignal\OneSignalServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            OneSignalServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('onesingle.app_id', 'test-app-id');
        $app['config']->set('onesingle.rest_api_key', 'test-rest-api-key');
        $app['config']->set('onesingle.api_url', 'https://onesignal.com/api/v1');
    }

    protected function defineRoutes($router): void
    {
        Route::prefix('api')->group(function (): void {
            Route::get('/onesignal/test', function () {
                $client = onesignal();

                return response()->json([
                    'ok' => true,
                    'message' => 'OneSignal package is working.',
                    'client' => $client::class,
                    'app_id_set' => config('onesingle.app_id') !== null && config('onesingle.app_id') !== '',
                ]);
            });
        });
    }
}
