<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Enums\PermissionName;
use App\Support\Enums\RoleName;
use Database\Seeders\Central\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    $this->target = User::factory()->create();
});

it('allows a super admin to manage users', function () {
    $user = User::factory()->create()->assignRole(RoleName::SuperAdmin->value);

    expect($user->can('viewAny', User::class))->toBeTrue()
        ->and($user->can('view', $this->target))->toBeTrue()
        ->and($user->can('create', User::class))->toBeTrue()
        ->and($user->can('update', $this->target))->toBeTrue()
        ->and($user->can('delete', $this->target))->toBeTrue();
});

it('denies a user without permissions', function () {
    $user = User::factory()->create();

    expect($user->can('viewAny', User::class))->toBeFalse()
        ->and($user->can('view', $this->target))->toBeFalse()
        ->and($user->can('create', User::class))->toBeFalse()
        ->and($user->can('update', $this->target))->toBeFalse()
        ->and($user->can('delete', $this->target))->toBeFalse();
});

it('only grants the abilities matching the given permissions', function () {
    $user = User::factory()->create()->givePermissionTo(PermissionName::UsersView);

    expect($user->can('viewAny', User::class))->toBeTrue()
        ->and($user->can('view', $this->target))->toBeTrue()
        ->and($user->can('create', User::class))->toBeFalse()
        ->and($user->can('update', $this->target))->toBeFalse()
        ->and($user->can('delete', $this->target))->toBeFalse();
});

it('does not allow a user to delete themselves', function () {
    $user = User::factory()->create()->assignRole(RoleName::SuperAdmin->value);

    expect($user->can('delete', $user))->toBeFalse()
        ->and($user->can('update', $user))->toBeTrue();
});
