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
});

function userWithRole(?RoleName $role): User
{
    $user = User::factory()->create();

    return $role ? $user->assignRole($role->value) : $user;
}

// Atores com todas as permissions de usuário, para isolar a regra de hierarquia
it('only manages users of equal or lower level', function (RoleName $actorRole, ?RoleName $targetRole, bool $allowed) {
    $actor = userWithRole($actorRole)->givePermissionTo(
        PermissionName::UsersUpdate, PermissionName::UsersToggleStatus, PermissionName::UsersDelete,
    );
    $target = userWithRole($targetRole);

    expect($actor->can('update', $target))->toBe($allowed)
        ->and($actor->can('toggleStatus', $target))->toBe($allowed)
        ->and($actor->can('delete', $target))->toBe($allowed);
})->with([
    'super-admin → super-admin' => [RoleName::SuperAdmin, RoleName::SuperAdmin, true],
    'super-admin → admin' => [RoleName::SuperAdmin, RoleName::Admin, true],
    'super-admin → employee' => [RoleName::SuperAdmin, RoleName::Employee, true],
    'super-admin → sem role' => [RoleName::SuperAdmin, null, true],
    'admin → super-admin' => [RoleName::Admin, RoleName::SuperAdmin, false],
    'admin → admin' => [RoleName::Admin, RoleName::Admin, true],
    'admin → employee' => [RoleName::Admin, RoleName::Employee, true],
    'admin → sem role' => [RoleName::Admin, null, true],
    'employee → super-admin' => [RoleName::Employee, RoleName::SuperAdmin, false],
    'employee → admin' => [RoleName::Employee, RoleName::Admin, false],
]);

it('uses the highest role when a user has several', function () {
    $actor = userWithRole(RoleName::Admin);
    $target = userWithRole(RoleName::Employee)->assignRole(RoleName::SuperAdmin->value);

    expect($actor->can('update', $target))->toBeFalse();
});

it('still requires the permission besides the level', function () {
    $employee = userWithRole(RoleName::Employee);

    expect($employee->can('update', userWithRole(null)))->toBeFalse();
});

it('blocks an admin from toggling a super admin through the API', function () {
    $superAdmin = userWithRole(RoleName::SuperAdmin);

    $this->actingAs(userWithRole(RoleName::Admin), 'api')
        ->patchJson("/api/v1/users/{$superAdmin->uuid}/toggle-status")
        ->assertForbidden();

    expect($superAdmin->fresh()->is_active)->toBeTrue();
});

it('lets an admin toggle an employee through the API', function () {
    $employee = userWithRole(RoleName::Employee);

    $this->actingAs(userWithRole(RoleName::Admin), 'api')
        ->patchJson("/api/v1/users/{$employee->uuid}/toggle-status")
        ->assertOk();

    expect($employee->fresh()->is_active)->toBeFalse();
});

it('deletes through the API only when authorized', function () {
    $admin = userWithRole(RoleName::Admin);
    $target = userWithRole(RoleName::Employee);

    $this->actingAs($admin, 'api')
        ->deleteJson("/api/v1/users/{$target->uuid}")
        ->assertForbidden();

    $this->actingAs(userWithRole(RoleName::SuperAdmin), 'api')
        ->deleteJson("/api/v1/users/{$target->uuid}")
        ->assertNoContent();

    $this->assertSoftDeleted($target);
});

it('does not allow a user to toggle their own status', function (RoleName $role) {
    $user = userWithRole($role)->givePermissionTo(PermissionName::UsersToggleStatus);

    expect($user->can('toggleStatus', $user))->toBeFalse();

    $this->actingAs($user, 'api')
        ->patchJson("/api/v1/users/{$user->uuid}/toggle-status")
        ->assertForbidden();

    expect($user->fresh()->is_active)->toBeTrue();
})->with([RoleName::SuperAdmin, RoleName::Admin]);
