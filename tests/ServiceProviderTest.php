<?php

declare(strict_types=1);

namespace Jowxidea\Onesignal\Tests;

use Jowxidea\Onesignal\OneSignalClient;

class ServiceProviderTest extends TestCase
{
    public function test_client_is_bound_in_container(): void
    {
        $client = $this->app->make('onesignal');

        $this->assertInstanceOf(OneSignalClient::class, $client);
    }

    public function test_helper_returns_client_instance(): void
    {
        $this->assertTrue(function_exists('onesignal'));
        $this->assertInstanceOf(OneSignalClient::class, onesignal());
    }
}

