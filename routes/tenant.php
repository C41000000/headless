<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\Auth\LoginController;
use App\Http\Controllers\Tenant\Users\DeleteUserController;
use App\Http\Controllers\Tenant\Users\IndexUserController;
use App\Http\Controllers\Tenant\Users\ShowUserController;
use App\Http\Controllers\Tenant\Users\StoreUserController;
use App\Http\Controllers\Tenant\Users\ToggleUserStatusController;
use App\Http\Controllers\Tenant\Users\UpdateUserController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

$centralDomains = implode('|', array_map(fn (string $d) => preg_quote($d, '#'), (array) config('tenancy.central_domains', [])));
$tenantDomainPattern = '^(?!('.$centralDomains.')$).*';

Route::domain('{tenant_domain}')
    ->where(['tenant_domain' => $tenantDomainPattern])
    ->middleware([
        'api',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
    ])
    ->prefix('api/v1')
    ->name('tenant.v1.')
    ->group(function () {
        Route::post('auth/login', LoginController::class)
            ->middleware('throttle:login')
            ->name('auth.login');

        Route::middleware('auth:tenant_api')->group(function () {
            Route::get('users', IndexUserController::class)->name('users.index');
            Route::post('users', StoreUserController::class)->name('users.store');
            Route::get('users/{user}', ShowUserController::class)->name('users.show');
            Route::patch('users/{user}', UpdateUserController::class)->name('users.update');
            Route::delete('users/{user}', DeleteUserController::class)->name('users.destroy');
            Route::patch('users/{user}/toggle-status', ToggleUserStatusController::class)->name('users.toggle-status');
        });
    });
