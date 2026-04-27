<?php

declare(strict_types=1);

namespace Jowxidea\Onesignal\Tests;

use Illuminate\Support\Facades\Http;
use Jowxidea\Onesignal\OneSignalClient;

class OneSignalClientTest extends TestCase
{
    public function test_send_to_all_posts_notification_payload(): void
    {
        Http::fake([
            'https://onesignal.com/api/v1/notifications' => Http::response([
                'id' => 'notification-id-all',
            ], 200),
        ]);

        $client = $this->app->make(OneSignalClient::class);

        $response = $client->sendToAll(
            ['en' => 'Hello everyone'],
            ['en' => 'Broadcast'],
            ['source' => 'phpunit']
        );

        $this->assertSame('notification-id-all', $response['id']);

        Http::assertSent(function ($request): bool {
            $data = $request->data();

            return $request->url() === 'https://onesignal.com/api/v1/notifications'
                && $data['app_id'] === 'test-app-id'
                && $data['included_segments'] === ['All']
                && $data['contents']['en'] === 'Hello everyone';
        });
    }

    public function test_send_to_one_user_posts_notification_payload(): void
    {
        Http::fake([
            'https://onesignal.com/api/v1/notifications' => Http::response([
                'id' => 'notification-id-one',
            ], 200),
        ]);

        $client = $this->app->make(OneSignalClient::class);

        $response = $client->sendToUser(
            'player-id-1',
            ['en' => 'Hello one user']
        );

        $this->assertSame('notification-id-one', $response['id']);

        Http::assertSent(function ($request): bool {
            $data = $request->data();

            return $request->url() === 'https://onesignal.com/api/v1/notifications'
                && $data['app_id'] === 'test-app-id'
                && $data['include_player_ids'] === ['player-id-1']
                && $data['contents']['en'] === 'Hello one user';
        });
    }

    public function test_send_to_many_users_posts_notification_payload(): void
    {
        Http::fake([
            'https://onesignal.com/api/v1/notifications' => Http::response([
                'id' => 'notification-id-many',
                'recipients' => 2,
            ], 200),
        ]);

        $client = $this->app->make(OneSignalClient::class);

        $response = $client->sendToUsers(
            ['player-id-1', 'player-id-2'],
            ['en' => 'Hello from tests'],
            ['en' => 'Test heading'],
            ['source' => 'phpunit']
        );

        $this->assertSame('notification-id-many', $response['id']);

        Http::assertSent(function ($request): bool {
            $data = $request->data();

            return $request->url() === 'https://onesignal.com/api/v1/notifications'
                && $data['app_id'] === 'test-app-id'
                && $data['include_player_ids'] === ['player-id-1', 'player-id-2']
                && $data['contents']['en'] === 'Hello from tests';
        });
    }

    public function test_response_metadata_is_included_for_json_calls(): void
    {
        Http::fake([
            'https://onesignal.com/api/v1/notifications' => Http::response([
                'id' => 'notification-id-meta',
            ], 200, ['X-Test' => 'meta-header']),
        ]);

        $client = $this->app->make(OneSignalClient::class);

        $response = $client->sendToAll(
            ['en' => 'Hello metadata'],
            ['en' => 'Metadata'],
            ['source' => 'phpunit']
        );

        $this->assertSame('notification-id-meta', $response['id']);
        $this->assertSame(200, $response['_response']['status']);
        $this->assertTrue($response['_response']['successful']);
        $this->assertSame('meta-header', $response['_response']['headers']['X-Test'][0]);
    }

    public function test_response_metadata_is_included_for_non_json_calls(): void
    {
        Http::fake([
            'https://onesignal.com/api/v1/players/csv_export' => Http::response('csv-content', 200, ['Content-Type' => 'text/csv']),
        ]);

        $client = $this->app->make(OneSignalClient::class);

        $response = $client->requestPlayersCSV();

        $this->assertSame('csv-content', $response['body']);
        $this->assertSame(200, $response['_response']['status']);
        $this->assertSame('text/csv', $response['_response']['headers']['Content-Type'][0]);
    }

    public function test_legacy_send_notification_to_all_still_works(): void
    {
        Http::fake([
            'https://onesignal.com/api/v1/notifications' => Http::response([
                'id' => 'legacy-notification-id',
            ], 200),
        ]);

        $client = $this->app->make(OneSignalClient::class);

        $response = $client->sendNotificationToAll('Legacy message');

        $this->assertSame('legacy-notification-id', $response['id']);

        Http::assertSent(function ($request): bool {
            $data = $request->data();

            return $request->url() === 'https://onesignal.com/api/v1/notifications'
                && $data['contents']['en'] === 'Legacy message'
                && $data['included_segments'] === ['All'];
        });
    }
}
