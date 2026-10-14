<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Enums\RoleName;
use Database\Seeders\Central\RolesAndPermissionsSeeder;
use Database\Seeders\Central\SuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('auth.super_admin', [
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => 'secret-password',
    ]);

    $this->seed(RolesAndPermissionsSeeder::class);
});

it('creates a verified super admin only once', function () {
    $this->seed(SuperAdminSeeder::class);
    $this->seed(SuperAdminSeeder::class);

    $user = User::query()->sole();

    expect($user->email)->toBe('admin@example.com')
        ->and($user->email_verified_at)->not->toBeNull()
        ->and(Hash::check('secret-password', $user->password))->toBeTrue()
        ->and($user->hasRole(RoleName::SuperAdmin->value))->toBeTrue();
});

it('refuses to run without a password', function () {
    config()->set('auth.super_admin.password', null);

    $this->seed(SuperAdminSeeder::class);
})->throws(RuntimeException::class);
