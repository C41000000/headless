<?php

declare(strict_types=1);

use App\Http\Controllers\Central\Auth\LoginController;
use App\Http\Controllers\Central\Domains\DeleteDomainController;
use App\Http\Controllers\Central\Domains\IndexDomainController;
use App\Http\Controllers\Central\Domains\ShowDomainController;
use App\Http\Controllers\Central\Domains\StoreDomainController;
use App\Http\Controllers\Central\Domains\UpdateDomainController;
use App\Http\Controllers\Central\Tenants\DeleteTenantController;
use App\Http\Controllers\Central\Tenants\IndexTenantController;
use App\Http\Controllers\Central\Tenants\ShowTenantController;
use App\Http\Controllers\Central\Tenants\StoreTenantController;
use App\Http\Controllers\Central\Tenants\UpdateTenantController;
use App\Http\Controllers\Central\Users\DeleteUserController;
use App\Http\Controllers\Central\Users\ShowUserController;
use App\Http\Controllers\Central\Users\StoreUserController;
use App\Http\Controllers\Central\Users\ToggleUserStatusController;
use App\Http\Controllers\Central\Users\UpdateUserController;
use App\Http\Middleware\EnsureCentralDomain;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('v1.')->middleware(EnsureCentralDomain::class)->group(function () {
    Route::post('auth/login', LoginController::class)
        ->middleware('throttle:login')
        ->name('central.auth.login');

    Route::middleware('auth:api')->group(function () {
        Route::post('users', StoreUserController::class)->name('central.users.store');
        Route::patch('users/{user}', UpdateUserController::class)->name('central.users.update');
        Route::get('users/{user}', ShowUserController::class)->name('central.users.show');
        Route::delete('users/{user}', DeleteUserController::class)->name('central.users.destroy');
        Route::patch('users/{user}/toggle-status', ToggleUserStatusController::class)->name('central.users.toggle-status');

        Route::get('tenants', IndexTenantController::class)->name('central.tenants.index');
        Route::post('tenants', StoreTenantController::class)->name('central.tenants.store');
        Route::get('tenants/{tenant}', ShowTenantController::class)->name('central.tenants.show');
        Route::patch('tenants/{tenant}', UpdateTenantController::class)->name('central.tenants.update');
        Route::delete('tenants/{tenant}', DeleteTenantController::class)->name('central.tenants.destroy');

        // scopeBindings: o {domain} precisa pertencer ao {tenant} da URL, senão 404
        Route::scopeBindings()->group(function () {
            Route::get('tenants/{tenant}/domains', IndexDomainController::class)->name('central.tenants.domains.index');
            Route::post('tenants/{tenant}/domains', StoreDomainController::class)->name('central.tenants.domains.store');
            Route::get('tenants/{tenant}/domains/{domain}', ShowDomainController::class)->name('central.tenants.domains.show');
            Route::patch('tenants/{tenant}/domains/{domain}', UpdateDomainController::class)->name('central.tenants.domains.update');
            Route::delete('tenants/{tenant}/domains/{domain}', DeleteDomainController::class)->name('central.tenants.domains.destroy');
        });
    });
});
