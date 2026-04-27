<?php

declare(strict_types=1);

namespace Jowxidea\Onesignal\Tests;

class ApiRouteTest extends TestCase
{
    public function test_api_route_reports_package_is_working(): void
    {
        $response = $this->getJson('/api/onesignal/test');

        $response->assertOk();
        $response->assertJson([
            'ok' => true,
            'message' => 'OneSignal package is working.',
            'app_id_set' => true,
        ]);

        $response->assertJsonStructure([
            'ok',
            'message',
            'client',
            'app_id_set',
        ]);
    }
}

