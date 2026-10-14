<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Enums\PermissionName;
use App\Support\Enums\RoleName;
use Database\Seeders\Central\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('syncs roles and permissions with the enums', function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(RolesAndPermissionsSeeder::class);

    expect(Permission::query()->pluck('name')->sort()->values()->all())
        ->toBe(collect(PermissionName::cases())->pluck('value')->sort()->values()->all())
        ->and(Role::findByName(RoleName::SuperAdmin->value, 'api')->permissions)->toHaveCount(count(PermissionName::cases()));
});

it('removes permissions and roles that left the enums, with their links', function () {
    $this->seed(RolesAndPermissionsSeeder::class);

    Permission::findOrCreate('reports.view', 'api');
    $orphanRole = Role::findOrCreate('legacy', 'api')->givePermissionTo('reports.view');
    Role::findByName(RoleName::SuperAdmin->value, 'api')->givePermissionTo('reports.view');
    $user = User::factory()->create()->assignRole($orphanRole)->givePermissionTo('reports.view');

    $this->seed(RolesAndPermissionsSeeder::class);

    expect(Permission::query()->where('name', 'reports.view')->exists())->toBeFalse()
        ->and(Role::query()->where('name', 'legacy')->exists())->toBeFalse()
        ->and($user->fresh()->roles)->toBeEmpty()
        ->and($user->fresh()->permissions)->toBeEmpty()
        ->and(Role::findByName(RoleName::SuperAdmin->value, 'api')->permissions)->toHaveCount(count(PermissionName::cases()));
});

it('leaves other guards untouched', function () {
    Permission::findOrCreate('reports.view', 'web');
    Role::findOrCreate('legacy', 'web');

    $this->seed(RolesAndPermissionsSeeder::class);

    expect(Permission::query()->where(['name' => 'reports.view', 'guard_name' => 'web'])->exists())->toBeTrue()
        ->and(Role::query()->where(['name' => 'legacy', 'guard_name' => 'web'])->exists())->toBeTrue();
});

it('runs the seeder from migrate:wait', function () {
    $this->artisan('migrate:wait', ['--seeder' => RolesAndPermissionsSeeder::class])->assertSuccessful();

    expect(Role::query()->where('name', RoleName::SuperAdmin->value)->exists())->toBeTrue();
});

it('gives each role exactly the permissions defined in the enum', function (RoleName $role, array $expected) {
    $this->seed(RolesAndPermissionsSeeder::class);

    expect(Role::findByName($role->value, 'api')->permissions->pluck('name')->sort()->values()->all())
        ->toBe(collect($expected)->sort()->values()->all());
})->with([
    'admin' => [RoleName::Admin, [
        'users.view', 'users.create', 'users.update', 'users.toggle-status',
        'tenants.view', 'tenants.create', 'tenants.update',
        'domains.view', 'domains.create', 'domains.update',
    ]],
    'employee' => [RoleName::Employee, ['users.view', 'tenants.view', 'domains.view']],
]);

it('lets only the super admin delete', function (RoleName $role, bool $canDelete) {
    $this->seed(RolesAndPermissionsSeeder::class);

    $user = User::factory()->create()->assignRole($role->value);

    expect($user->can('delete', User::factory()->create()))->toBe($canDelete);
})->with([
    'super-admin' => [RoleName::SuperAdmin, true],
    'admin' => [RoleName::Admin, false],
    'employee' => [RoleName::Employee, false],
]);
