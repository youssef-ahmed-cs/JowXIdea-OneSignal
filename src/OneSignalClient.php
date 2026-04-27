<?php

declare(strict_types=1);

namespace Jowxidea\Onesignal;

use Closure;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use RuntimeException;

class OneSignalClient
{
    public const ENDPOINT_NOTIFICATIONS = 'notifications';
    public const ENDPOINT_PLAYERS = 'players';
    public const ENDPOINT_APPS = 'apps';

    public bool $requestAsync = false;
    public int $maxRetries = 2;
    public int $retryDelay = 500;

    private array $additionalParams = [];
    private ?Closure $requestCallback = null;

    public function __construct(
        private readonly string $appId,
        private readonly string $restApiKey,
        private readonly string $apiUrl,
        private readonly Factory $http,
        private readonly string $userAuthKey = '',
        private readonly int $timeout = 10
    ) {
    }

    public function async(bool $on = true): self
    {
        // Compatibility flag from the legacy API. Requests are still executed synchronously.
        $this->requestAsync = $on;

        return $this;
    }

    public function callback(callable $requestCallback): self
    {
        $this->requestCallback = Closure::fromCallable($requestCallback);

        return $this;
    }

    public function testCredentials(): string
    {
        return 'APP ID: ' . $this->appId . ' REST: ' . $this->restApiKey;
    }

    public function addParams(array $params = []): self
    {
        $this->additionalParams = $params;

        return $this;
    }

    public function setParam(string $key, mixed $value): self
    {
        $this->additionalParams[$key] = $value;

        return $this;
    }

    public function send(array $payload): array
    {
        return $this->sendNotificationCustom($payload);
    }

    public function sendToAll(array $contents, array $headings = [], array $data = []): array
    {
        return $this->send([
            'contents' => $contents,
            'headings' => $headings,
            'data' => $data,
            'included_segments' => ['All'],
        ]);
    }

    public function sendToAllUsers(array $contents, array $headings = [], array $data = []): array
    {
        return $this->sendToAll($contents, $headings, $data);
    }

    public function sendToUser(string $playerId, array $contents, array $headings = [], array $data = []): array
    {
        return $this->sendToUsers([$playerId], $contents, $headings, $data);
    }

    public function sendToUsers(array $playerIds, array $contents, array $headings = [], array $data = []): array
    {
        return $this->send([
            'contents' => $contents,
            'headings' => $headings,
            'data' => $data,
            'include_player_ids' => array_values($playerIds),
        ]);
    }

    public function sendToSegments(array $includedSegments, array $contents, array $headings = [], array $data = []): array
    {
        return $this->send([
            'contents' => $contents,
            'headings' => $headings,
            'data' => $data,
            'included_segments' => array_values($includedSegments),
        ]);
    }

    public function sendNotificationToUser(
        string $message,
        string|array $userId,
        ?string $url = null,
        ?array $data = null,
        ?array $buttons = null,
        ?string $schedule = null,
        ?string $headings = null,
        ?string $subtitle = null
    ): array {
        return $this->sendNotificationCustom($this->buildLegacyNotificationPayload(
            message: $message,
            target: ['include_player_ids' => is_array($userId) ? $userId : [$userId]],
            url: $url,
            data: $data,
            buttons: $buttons,
            schedule: $schedule,
            headings: $headings,
            subtitle: $subtitle
        ));
    }

    public function sendNotificationToExternalUser(
        string $message,
        string|array $userId,
        ?string $url = null,
        ?array $data = null,
        ?array $buttons = null,
        ?string $schedule = null,
        ?string $headings = null,
        ?string $subtitle = null
    ): array {
        return $this->sendNotificationCustom($this->buildLegacyNotificationPayload(
            message: $message,
            target: ['include_external_user_ids' => is_array($userId) ? $userId : [$userId]],
            url: $url,
            data: $data,
            buttons: $buttons,
            schedule: $schedule,
            headings: $headings,
            subtitle: $subtitle
        ));
    }

    public function sendNotificationUsingTags(
        string $message,
        array $tags,
        ?string $url = null,
        ?array $data = null,
        ?array $buttons = null,
        ?string $schedule = null,
        ?string $headings = null,
        ?string $subtitle = null
    ): array {
        return $this->sendNotificationCustom($this->buildLegacyNotificationPayload(
            message: $message,
            target: ['filters' => $tags],
            url: $url,
            data: $data,
            buttons: $buttons,
            schedule: $schedule,
            headings: $headings,
            subtitle: $subtitle
        ));
    }

    public function sendNotificationToAll(
        string $message,
        ?string $url = null,
        ?array $data = null,
        ?array $buttons = null,
        ?string $schedule = null,
        ?string $headings = null,
        ?string $subtitle = null
    ): array {
        return $this->sendNotificationCustom($this->buildLegacyNotificationPayload(
            message: $message,
            target: ['included_segments' => ['All']],
            url: $url,
            data: $data,
            buttons: $buttons,
            schedule: $schedule,
            headings: $headings,
            subtitle: $subtitle
        ));
    }

    public function sendNotificationToSegment(
        string $message,
        string $segment,
        ?string $url = null,
        ?array $data = null,
        ?array $buttons = null,
        ?string $schedule = null,
        ?string $headings = null,
        ?string $subtitle = null
    ): array {
        return $this->sendNotificationCustom($this->buildLegacyNotificationPayload(
            message: $message,
            target: ['included_segments' => [$segment]],
            url: $url,
            data: $data,
            buttons: $buttons,
            schedule: $schedule,
            headings: $headings,
            subtitle: $subtitle
        ));
    }

    public function deleteNotification(string $notificationId, ?string $appId = null): array
    {
        return $this->request('DELETE', self::ENDPOINT_NOTIFICATIONS . '/' . $notificationId, [], [
            'app_id' => $appId ?: $this->appId,
        ]);
    }

    public function sendNotificationCustom(array $parameters = []): array
    {
        $payload = array_merge($parameters, $this->additionalParams);

        if (!isset($payload['app_id'])) {
            $payload['app_id'] = $this->appId;
        }

        if (
            empty($payload['included_segments'])
            && empty($payload['include_player_ids'])
            && empty($payload['include_external_user_ids'])
            && empty($payload['include_aliases'])
        ) {
            $payload['included_segments'] = ['All'];
        }

        if (isset($payload['api_key']) && is_string($payload['api_key']) && $payload['api_key'] !== '') {
            $apiKey = $payload['api_key'];
            unset($payload['api_key']);

            return $this->request('POST', self::ENDPOINT_NOTIFICATIONS, $payload, [], $apiKey);
        }

        return $this->request('POST', self::ENDPOINT_NOTIFICATIONS, $payload);
    }

    public function getNotification(string $notificationId, ?string $appId = null): array
    {
        return $this->request('GET', self::ENDPOINT_NOTIFICATIONS . '/' . $notificationId, [], [
            'app_id' => $appId ?: $this->appId,
        ]);
    }

    public function getNotifications(?string $appId = null, ?int $limit = null, ?int $offset = null): array
    {
        $query = [
            'app_id' => $appId ?: $this->appId,
        ];

        if ($limit !== null) {
            $query['limit'] = $limit;
        }

        if ($offset !== null) {
            $query['offset'] = $offset;
        }

        return $this->request('GET', self::ENDPOINT_NOTIFICATIONS, [], $query);
    }

    public function getApp(?string $appId = null): array
    {
        return $this->request('GET', self::ENDPOINT_APPS . '/' . ($appId ?: $this->appId), [], [], null, true);
    }

    public function getApps(): array
    {
        return $this->request('GET', self::ENDPOINT_APPS, [], [], null, true);
    }

    public function createPlayer(array $parameters): array
    {
        if (!isset($parameters['device_type']) || !is_numeric($parameters['device_type'])) {
            throw new RuntimeException('The `device_type` param is required as integer to create a player(device)');
        }

        return $this->sendPlayer($parameters, 'POST', self::ENDPOINT_PLAYERS);
    }

    public function editPlayer(array $parameters): array
    {
        if (!isset($parameters['id']) || !is_string($parameters['id'])) {
            throw new RuntimeException('The `id` param is required to edit a player(device)');
        }

        return $this->sendPlayer($parameters, 'PUT', self::ENDPOINT_PLAYERS . '/' . $parameters['id']);
    }

    public function requestPlayersCSV(?string $appId = null, ?array $parameters = null): array
    {
        $payload = $parameters ?? [];
        $payload['app_id'] = $appId ?: $this->appId;

        return $this->request('POST', self::ENDPOINT_PLAYERS . '/csv_export', $payload);
    }

    public function post(string $endPoint, array $payload = []): array
    {
        return $this->request('POST', ltrim($endPoint, '/'), $payload);
    }

    public function put(string $endPoint, array $payload = []): array
    {
        return $this->request('PUT', ltrim($endPoint, '/'), $payload);
    }

    public function get(string $endPoint, array $query = []): array
    {
        return $this->request('GET', ltrim($endPoint, '/'), [], $query);
    }

    public function delete(string $endPoint, array $query = []): array
    {
        return $this->request('DELETE', ltrim($endPoint, '/'), [], $query);
    }

    private function sendPlayer(array $parameters, string $method, string $endpoint): array
    {
        $payload = $parameters;
        $payload['app_id'] = $this->appId;

        return $this->request($method, $endpoint, $payload);
    }

    private function buildLegacyNotificationPayload(
        string $message,
        array $target,
        ?string $url,
        ?array $data,
        ?array $buttons,
        ?string $schedule,
        ?string $headings,
        ?string $subtitle
    ): array {
        $payload = array_merge([
            'contents' => ['en' => $message],
        ], $target);

        if ($url !== null) {
            $payload['url'] = $url;
        }

        if ($data !== null) {
            $payload['data'] = $data;
        }

        if ($buttons !== null) {
            $payload['buttons'] = $buttons;
        }

        if ($schedule !== null) {
            $payload['send_after'] = $schedule;
        }

        if ($headings !== null) {
            $payload['headings'] = ['en' => $headings];
        }

        if ($subtitle !== null) {
            $payload['subtitle'] = ['en' => $subtitle];
        }

        return $payload;
    }

    private function request(
        string $method,
        string $endpoint,
        array $payload = [],
        array $query = [],
        ?string $overrideApiKey = null,
        bool $userAuth = false
    ): array {
        $request = $this->baseRequest($overrideApiKey, $userAuth);
        $method = strtoupper($method);
        $endpoint = ltrim($endpoint, '/');

        if ($query !== []) {
            $request = $request->withQueryParameters($query);
        }

        $response = match ($method) {
            'GET' => $request->get($endpoint),
            'DELETE' => $request->delete($endpoint),
            'PUT' => $request->put($endpoint, $payload),
            default => $request->post($endpoint, $payload),
        };

        $response = $response->throw();

        if ($this->requestCallback !== null) {
            ($this->requestCallback)($response);
        }

        return $this->normalizeResponse($response);
    }

    private function baseRequest(?string $overrideApiKey = null, bool $userAuth = false): PendingRequest
    {
        $apiKey = $overrideApiKey;

        if ($apiKey === null) {
            $apiKey = $userAuth ? $this->userAuthKey : $this->restApiKey;
        }

        if ($apiKey === '') {
            throw new RuntimeException($userAuth
                ? 'OneSignal user auth key is missing. Set ONESIGNAL_USER_AUTH_KEY.'
                : 'OneSignal credentials are missing. Set ONESIGNAL_APP_ID and ONESIGNAL_REST_API_KEY.'
            );
        }

        $request = $this->http
            ->baseUrl(rtrim($this->apiUrl, '/'))
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'Authorization' => 'Basic ' . $apiKey,
            ])
            ->retry($this->maxRetries, $this->retryDelay, throw: false);

        if ($this->timeout > 0) {
            $request = $request->timeout($this->timeout);
        }

        return $request;
    }

    private function normalizeResponse(Response $response): array
    {
        $json = $response->json();

        $result = is_array($json)
            ? $json
            : ['body' => $response->body()];

        $result['_response'] = [
            'status' => $response->status(),
            'ok' => $response->ok(),
            'successful' => $response->successful(),
            'redirect' => $response->redirect(),
            'headers' => $response->headers(),
        ];

        return $result;
    }
}
