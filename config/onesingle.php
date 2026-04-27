<?php

return [
    'app_id' => env('ONESIGNAL_APP_ID'),
    'rest_api_key' => env('ONESIGNAL_REST_API_KEY'),
    'api_url' => env('ONESIGNAL_API_URL', 'https://onesignal.com/api/v1'),
    'default_channel' => env('ONESIGNAL_DEFAULT_CHANNEL', 'push'),
];
