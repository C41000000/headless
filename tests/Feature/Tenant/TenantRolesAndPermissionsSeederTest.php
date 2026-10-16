<?php

declare(strict_types=1);

use App\Models\Central\Tenant;
use App\Support\Enums\Tenant\PermissionName;
use App\Support\Enums\Tenant\RoleName;
use Database\Seeders\Central\RolesAndPermissionsSeeder as CentralRolesSeeder;
use Database\Seeders\Tenant\RolesAndPermissionsSeeder as TenantRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CentralRolesSeeder::class);
});

it('syncs tenant roles and permissions with the tenant enums', function () {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    $tenant->run(function () {
        $guard = 'tenant_api';

        $dbPermissions = Permission::query()->where('guard_name', $guard)->pluck('name')->sort()->values();
        $enumPermissions = collect(PermissionName::cases())->map->value->sort()->values();

        expect($dbPermissions->all())->toBe($enumPermissions->all());

        $dbRoles = Role::query()->where('guard_name', $guard)->pluck('name')->sort()->values();
        $enumRoles = collect(RoleName::cases())->map->value->sort()->values();

        expect($dbRoles->all())->toBe($enumRoles->all());
    });
});

it('removes permissions and roles that left tenant enums with their links', function () {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    $tenant->run(function () {
        $guard = 'tenant_api';

        $orphanPermission = Permission::create(['name' => 'obsoleta.ver', 'guard_name' => $guard]);
        $orphanRole = Role::create(['name' => 'estagiario', 'guard_name' => $guard]);
        $orphanRole->givePermissionTo($orphanPermission);

        $this->seed(TenantRolesSeeder::class);

        expect(Permission::query()->where('name', 'obsoleta.ver')->where('guard_name', $guard)->exists())->toBeFalse()
            ->and(Role::query()->where('name', 'estagiario')->where('guard_name', $guard)->exists())->toBeFalse()
            ->and(DB::table('role_has_permissions')->where('permission_id', $orphanPermission->id)->exists())->toBeFalse();
    });
});

it('leaves other guards untouched in tenant database', function () {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    $tenant->run(function () {
        Permission::create(['name' => 'custom.permission', 'guard_name' => 'custom_guard']);
        Role::create(['name' => 'custom.role', 'guard_name' => 'custom_guard']);

        $this->seed(TenantRolesSeeder::class);

        expect(Permission::query()->where('guard_name', 'custom_guard')->exists())->toBeTrue()
            ->and(Role::query()->where('guard_name', 'custom_guard')->exists())->toBeTrue();
    });
});

it('gives each tenant role exactly the permissions defined in the enum', function (RoleName $roleName) {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    $tenant->run(function () use ($roleName) {
        $role = Role::findByName($roleName->value, 'tenant_api');
        $expected = collect($roleName->permissions())->map->value->sort()->values()->all();
        $actual = $role->permissions->pluck('name')->sort()->values()->all();

        expect($actual)->toBe($expected);
    });
})->with(RoleName::cases());
