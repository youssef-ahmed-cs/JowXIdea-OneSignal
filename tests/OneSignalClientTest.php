<?php

declare(strict_types=1);

namespace Jowxidea\Onesignal\Tests;

use Illuminate\Support\Facades\Http;
use Jowxidea\Onesignal\OneSignalClient;

class OneSignalClientTest extends TestCase
{
    public function test_send_to_users_posts_notification_payload(): void
    {
        Http::fake([
            'https://onesignal.com/api/v1/notifications' => Http::response([
                'id' => 'notification-id-123',
                'recipients' => 1,
            ], 200),
        ]);

        $client = $this->app->make(OneSignalClient::class);

        $response = $client->sendToUsers(
            ['player-id-1'],
            ['en' => 'Hello from tests'],
            ['en' => 'Test heading'],
            ['source' => 'phpunit']
        );

        $this->assertSame('notification-id-123', $response['id']);

        Http::assertSent(function ($request): bool {
            $data = $request->data();

            return $request->url() === 'https://onesignal.com/api/v1/notifications'
                && $data['app_id'] === 'test-app-id'
                && $data['include_player_ids'] === ['player-id-1']
                && $data['contents']['en'] === 'Hello from tests';
        });
    }
}

