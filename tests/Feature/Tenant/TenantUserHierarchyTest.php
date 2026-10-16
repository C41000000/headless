<?php

declare(strict_types=1);

use App\Models\Central\Tenant;
use App\Models\Tenant\User as TenantUser;
use App\Support\Enums\Tenant\PermissionName;
use App\Support\Enums\Tenant\RoleName;
use Database\Seeders\Central\RolesAndPermissionsSeeder as CentralRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CentralRolesSeeder::class);
});

function tenantUserWithRole(?RoleName $role): TenantUser
{
    $user = TenantUser::factory()->create();

    return $role ? $user->assignRole($role->value) : $user;
}

it('only manages tenant users of equal or lower level', function (RoleName $actorRole, ?RoleName $targetRole, bool $allowed) {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    $tenant->run(function () use ($actorRole, $targetRole, $allowed) {
        $actor = tenantUserWithRole($actorRole)->givePermissionTo(
            PermissionName::UsersUpdate,
            PermissionName::UsersToggleStatus,
            PermissionName::UsersDelete,
        );
        $target = tenantUserWithRole($targetRole);

        expect($actor->can('update', $target))->toBe($allowed)
            ->and($actor->can('toggleStatus', $target))->toBe($allowed)
            ->and($actor->can('delete', $target))->toBe($allowed);
    });
})->with([
    'admin → admin' => [RoleName::Admin, RoleName::Admin, true],
    'admin → employee' => [RoleName::Admin, RoleName::Employee, true],
    'admin → sem role' => [RoleName::Admin, null, true],
    'employee → admin' => [RoleName::Employee, RoleName::Admin, false],
    'employee → employee' => [RoleName::Employee, RoleName::Employee, true],
    'employee → sem role' => [RoleName::Employee, null, true],
]);

it('still requires permission besides level', function () {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    $tenant->run(function () {
        $employee = tenantUserWithRole(RoleName::Employee);
        $target = tenantUserWithRole(null);

        expect($employee->can('update', $target))->toBeFalse();
    });
});

it('blocks an employee from toggling an admin through the API', function () {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    [$admin, $employee] = $tenant->run(function () {
        return [
            tenantUserWithRole(RoleName::Admin),
            tenantUserWithRole(RoleName::Employee),
        ];
    });

    $this->actingAs($employee, 'tenant_api')
        ->patchJson("http://empresa.localhost/api/v1/users/{$admin->uuid}/toggle-status")
        ->assertForbidden();

    $tenant->run(function () use ($admin) {
        expect($admin->fresh()->is_active)->toBeTrue();
    });
});

it('lets an admin toggle an employee through the API', function () {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    [$admin, $employee] = $tenant->run(function () {
        return [
            tenantUserWithRole(RoleName::Admin),
            tenantUserWithRole(RoleName::Employee),
        ];
    });

    $this->actingAs($admin, 'tenant_api')
        ->patchJson("http://empresa.localhost/api/v1/users/{$employee->uuid}/toggle-status")
        ->assertOk();

    $tenant->run(function () use ($employee) {
        expect($employee->fresh()->is_active)->toBeFalse();
    });
});

it('deletes through the API only when authorized and soft deletes', function () {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    [$admin, $employee, $target] = $tenant->run(function () {
        return [
            tenantUserWithRole(RoleName::Admin),
            tenantUserWithRole(RoleName::Employee),
            tenantUserWithRole(RoleName::Employee),
        ];
    });

    $this->actingAs($employee, 'tenant_api')
        ->deleteJson("http://empresa.localhost/api/v1/users/{$target->uuid}")
        ->assertForbidden();

    $this->actingAs($admin, 'tenant_api')
        ->deleteJson("http://empresa.localhost/api/v1/users/{$target->uuid}")
        ->assertNoContent();

    $tenant->run(function () use ($target) {
        $this->assertSoftDeleted($target);
    });
});

it('does not allow a tenant user to toggle their own status', function () {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    $admin = $tenant->run(function () {
        return tenantUserWithRole(RoleName::Admin);
    });

    expect($admin->can('toggleStatus', $admin))->toBeFalse();

    $this->actingAs($admin, 'tenant_api')
        ->patchJson("http://empresa.localhost/api/v1/users/{$admin->uuid}/toggle-status")
        ->assertForbidden();

    $tenant->run(function () use ($admin) {
        expect($admin->fresh()->is_active)->toBeTrue();
    });
});

it('does not allow a tenant user to delete themselves', function () {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    $admin = $tenant->run(function () {
        return tenantUserWithRole(RoleName::Admin);
    });

    expect($admin->can('delete', $admin))->toBeFalse();

    $this->actingAs($admin, 'tenant_api')
        ->deleteJson("http://empresa.localhost/api/v1/users/{$admin->uuid}")
        ->assertForbidden();

    $tenant->run(function () use ($admin) {
        $this->assertNotSoftDeleted($admin);
    });
});
