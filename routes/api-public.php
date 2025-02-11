<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

use LaravelReady\LicenseServer\Http\Controllers\Api\AuthController;
use LaravelReady\LicenseServer\Http\Controllers\Api\LicenseValidationController;
use LaravelReady\LicenseServer\Http\Middleware\LicenseAuthMiddleware;

/**
 * Public routes for License Server connector package
 *
 * This routes using for login, list
 */
Route::prefix('api/license-server')
    ->name('license-server.')
    ->middleware([
        'api',
        'throttle:60,1',
    ])
    ->group(function () {
        Route::prefix('auth')->name('auth.')->group(function () {
            Route::post('login', [AuthController::class, 'login'])->name('login');
        });

        $licenseController = Config::get('license-server.controllers.license_validation');

        $licenseController = $licenseController && is_array($licenseController)
            ? $licenseController
            : [LicenseValidationController::class, 'licenseValidate'];

        $licenseMiddlewares = [
            LicenseAuthMiddleware::class,
            'ls-license-guard',
        ];

        $addionalMiddlewares = Config::get('license-server.license_middlewares', []);

        if ($addionalMiddlewares && count($addionalMiddlewares)) {
            $licenseMiddlewares = array_merge($licenseMiddlewares, $addionalMiddlewares);
        }

        Route::post('license', $licenseController);
    });
