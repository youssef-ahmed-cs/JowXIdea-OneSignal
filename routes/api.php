<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function (): void {
    Route::get('/onesignal/test', function () {
        $client = onesignal();

        return response()->json([
            'ok' => true,
            'message' => 'OneSignal package is working.',
            'client' => $client::class,
            'app_id_set' => config('onesingle.app_id') !== null && config('onesingle.app_id') !== '',
        ]);
    });
});
