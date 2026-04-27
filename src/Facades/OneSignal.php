<?php

declare(strict_types=1);

namespace Jowxidea\Onesignal\Facades;

use Illuminate\Support\Facades\Facade;

class OneSignal extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'onesignal';
    }
}

