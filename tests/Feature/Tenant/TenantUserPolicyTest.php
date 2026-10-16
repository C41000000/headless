<?php

declare(strict_types=1);

use App\Models\Central\Tenant;
use App\Models\Tenant\User;
use App\Support\Enums\Tenant\PermissionName;
use App\Support\Enums\Tenant\RoleName;
use Database\Seeders\Central\RolesAndPermissionsSeeder as CentralRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CentralRolesSeeder::class);
});

it('allows a tenant admin to manage users', function () {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    $tenant->run(function () {
        $admin = User::factory()->create()->assignRole(RoleName::Admin->value);
        $other = User::factory()->create();

        expect($admin->can('viewAny', User::class))->toBeTrue()
            ->and($admin->can('view', $other))->toBeTrue()
            ->and($admin->can('create', User::class))->toBeTrue()
            ->and($admin->can('update', $other))->toBeTrue()
            ->and($admin->can('toggleStatus', $other))->toBeTrue()
            ->and($admin->can('delete', $other))->toBeTrue();
    });
});

it('denies a tenant user without permissions', function () {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    $tenant->run(function () {
        $user = User::factory()->create();
        $other = User::factory()->create();

        expect($user->can('viewAny', User::class))->toBeFalse()
            ->and($user->can('view', $other))->toBeFalse()
            ->and($user->can('create', User::class))->toBeFalse()
            ->and($user->can('update', $other))->toBeFalse()
            ->and($user->can('toggleStatus', $other))->toBeFalse()
            ->and($user->can('delete', $other))->toBeFalse();
    });
});

it('only grants the abilities matching the given permissions in tenant', function () {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    $tenant->run(function () {
        $user = User::factory()->create()->givePermissionTo(PermissionName::UsersView, PermissionName::UsersCreate);
        $other = User::factory()->create();

        expect($user->can('viewAny', User::class))->toBeTrue()
            ->and($user->can('view', $other))->toBeTrue()
            ->and($user->can('create', User::class))->toBeTrue()
            ->and($user->can('update', $other))->toBeFalse()
            ->and($user->can('toggleStatus', $other))->toBeFalse()
            ->and($user->can('delete', $other))->toBeFalse();
    });
});

it('does not allow a tenant user to delete themselves', function () {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    $tenant->run(function () {
        $user = User::factory()->create()->givePermissionTo(PermissionName::UsersDelete);

        expect($user->can('delete', $user))->toBeFalse();
    });
});
