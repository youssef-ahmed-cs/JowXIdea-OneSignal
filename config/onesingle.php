<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OneSignal App ID
    |--------------------------------------------------------------------------
    |
    | Your OneSignal application identifier. This value is required for all
    | notification requests and is available in your OneSignal dashboard.
    |
    */
    'app_id' => env('ONESIGNAL_APP_ID'),

    /*
    |--------------------------------------------------------------------------
    | OneSignal REST API Key
    |--------------------------------------------------------------------------
    |
    | The REST API key used to authorize notification requests.
    | Keep this value secret and load it from environment variables.
    |
    */
    'rest_api_key' => env('ONESIGNAL_REST_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | OneSignal User Auth Key
    |--------------------------------------------------------------------------
    |
    | Optional key for account-level endpoints such as app management APIs.
    | Leave empty if your application only sends notifications.
    |
    */
    'user_auth_key' => env('ONESIGNAL_USER_AUTH_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | OneSignal API Base URL
    |--------------------------------------------------------------------------
    |
    | Base URL for the OneSignal REST API. You should not need to change this
    | unless you are using a custom proxy or testing endpoint.
    |
    */
    'api_url' => env('ONESIGNAL_API_URL', 'https://onesignal.com/api/v1'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout (Seconds)
    |--------------------------------------------------------------------------
    |
    | Maximum number of seconds the HTTP client should wait for a response
    | from OneSignal before timing out.
    |
    */
    'timeout' => (int) env('ONESIGNAL_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Default Channel
    |--------------------------------------------------------------------------
    |
    | Default notification channel name used by your package/application logic.
    | This is metadata for your own usage and is not sent to OneSignal directly.
    |
    */
    'default_channel' => env('ONESIGNAL_DEFAULT_CHANNEL', 'push'),
];
