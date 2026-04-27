<?php

declare(strict_types=1);

use Jowxidea\Onesignal\OneSignalClient;

if (!function_exists('onesignal')) {
    function onesignal(): OneSignalClient
    {
        return app('onesignal');
    }
}

