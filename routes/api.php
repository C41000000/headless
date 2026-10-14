<?php

declare(strict_types=1);

use App\Http\Controllers\Central\Auth\LoginController;
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
    });
});
