<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\Auth\LoginController;
use App\Http\Controllers\Tenant\Banners\DeleteBannerController;
use App\Http\Controllers\Tenant\Banners\IndexBannerController;
use App\Http\Controllers\Tenant\Banners\IndexPublicBannerController;
use App\Http\Controllers\Tenant\Banners\ShowBannerController;
use App\Http\Controllers\Tenant\Banners\StoreBannerController;
use App\Http\Controllers\Tenant\Banners\UpdateBannerController;
use App\Http\Controllers\Tenant\Users\DeleteUserController;
use App\Http\Controllers\Tenant\Users\IndexUserController;
use App\Http\Controllers\Tenant\Users\ShowUserController;
use App\Http\Controllers\Tenant\Users\StoreUserController;
use App\Http\Controllers\Tenant\Users\ToggleUserStatusController;
use App\Http\Controllers\Tenant\Users\UpdateUserController;
use App\Http\Controllers\Tenant\VisualIdentity\ShowPublicVisualIdentityController;
use App\Http\Controllers\Tenant\VisualIdentity\ShowVisualIdentityController;
use App\Http\Controllers\Tenant\VisualIdentity\UpdateVisualIdentityController;
use App\Http\Middleware\StripTenantDomainParameter;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

$centralDomains = implode('|', array_map(fn (string $d) => preg_quote($d, '#'), (array) config('tenancy.central_domains', [])));
$tenantDomainPattern = '^(?!('.$centralDomains.')$).*';

Route::domain('{tenant_domain}')
    ->where(['tenant_domain' => $tenantDomainPattern])
    ->middleware([
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        StripTenantDomainParameter::class,
        'api',
    ])
    ->prefix('api/v1')
    ->name('tenant.v1.')
    ->group(function () {
        Route::post('auth/login', LoginController::class)
            ->middleware('throttle:login')
            ->name('auth.login');

        // Endpoints públicos de identidade visual e banners
        Route::get('visual-identity', ShowPublicVisualIdentityController::class)->name('visual-identity.show');
        Route::get('banners', IndexPublicBannerController::class)->name('banners.index');

        Route::middleware('auth:tenant_api')->group(function () {
            // Gestão de usuários do tenant
            Route::get('users', IndexUserController::class)->name('users.index');
            Route::post('users', StoreUserController::class)->name('users.store');
            Route::get('users/{user}', ShowUserController::class)->name('users.show');
            Route::patch('users/{user}', UpdateUserController::class)->name('users.update');
            Route::delete('users/{user}', DeleteUserController::class)->name('users.destroy');
            Route::patch('users/{user}/toggle-status', ToggleUserStatusController::class)->name('users.toggle-status');

            // Gestão de identidade visual (Admin)
            Route::get('admin/visual-identity', ShowVisualIdentityController::class)->name('admin.visual-identity.show');
            Route::patch('admin/visual-identity', UpdateVisualIdentityController::class)->name('admin.visual-identity.update');

            // Gestão de banners (Admin)
            Route::get('admin/banners', IndexBannerController::class)->name('admin.banners.index');
            Route::post('admin/banners', StoreBannerController::class)->name('admin.banners.store');
            Route::get('admin/banners/{banner}', ShowBannerController::class)->name('admin.banners.show');
            Route::patch('admin/banners/{banner}', UpdateBannerController::class)->name('admin.banners.update');
            Route::delete('admin/banners/{banner}', DeleteBannerController::class)->name('admin.banners.destroy');
        });
    });
