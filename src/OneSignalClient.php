<?php

declare(strict_types=1);

namespace Jowxidea\Onesignal;

use Illuminate\Http\Client\Factory;
use RuntimeException;

class OneSignalClient
{
    public function __construct(
        private readonly string $appId,
        private readonly string $restApiKey,
        private readonly string $apiUrl,
        private readonly Factory $http
    ) {
    }

    public function send(array $payload): array
    {
        if ($this->appId === '' || $this->restApiKey === '') {
            throw new RuntimeException('OneSignal credentials are missing. Set ONESIGNAL_APP_ID and ONESIGNAL_REST_API_KEY.');
        }

        $requestPayload = array_merge([
            'app_id' => $this->appId,
        ], $payload);

        return $this->http
            ->withToken($this->restApiKey)
            ->acceptJson()
            ->asJson()
            ->post(rtrim($this->apiUrl, '/') . '/notifications', $requestPayload)
            ->throw()
            ->json();
    }

    public function sendToUsers(array $playerIds, array $contents, array $headings = [], array $data = []): array
    {
        return $this->send([
            'include_player_ids' => array_values($playerIds),
            'contents' => $contents,
            'headings' => $headings,
            'data' => $data,
        ]);
    }

    public function sendToSegments(array $includedSegments, array $contents, array $headings = [], array $data = []): array
    {
        return $this->send([
            'included_segments' => array_values($includedSegments),
            'contents' => $contents,
            'headings' => $headings,
            'data' => $data,
        ]);
    }
}

