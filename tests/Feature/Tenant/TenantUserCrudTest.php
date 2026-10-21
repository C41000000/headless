<?php

declare(strict_types=1);

use App\Models\Central\Tenant;
use App\Models\Tenant\User as TenantUser;
use App\Models\User;
use App\Support\Enums\PermissionName;
use App\Support\Enums\Tenant\RoleName;
use Database\Seeders\Central\RolesAndPermissionsSeeder as CentralRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

const TENANT_API = 'http://empresa.localhost/api/v1';

beforeEach(function () {
    $this->seed(CentralRolesSeeder::class);

    $this->tenant = Tenant::query()->create(['name' => 'Empresa']);
    $this->tenant->domains()->create(['domain' => 'empresa.localhost']);
});

function tenantMember(Tenant $tenant, ?RoleName $role, array $attributes = []): TenantUser
{
    return $tenant->run(function () use ($role, $attributes) {
        $user = TenantUser::factory()->create($attributes);

        return $role ? $user->assignRole($role->value) : $user;
    });
}

it('lists tenant users with role and abilities, filtered by search, role and status', function () {
    $admin = tenantMember($this->tenant, RoleName::Admin, ['name' => 'Ana']);
    tenantMember($this->tenant, RoleName::Employee, ['name' => 'Bruno', 'email' => 'bruno@loja.com']);
    tenantMember($this->tenant, RoleName::Employee, ['name' => 'Carla', 'is_active' => false]);

    $this->actingAs($admin, 'tenant_api');

    $this->getJson(TENANT_API.'/users')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.role', 'admin')
        ->assertJsonPath('data.0.abilities', ['update' => true, 'toggle_status' => false, 'delete' => false])
        ->assertJsonPath('data.1.abilities', ['update' => true, 'toggle_status' => true, 'delete' => true]);

    $this->getJson(TENANT_API.'/users?search=LOJA')->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Bruno');
    $this->getJson(TENANT_API.'/users?role=employee')->assertJsonCount(2, 'data');
    $this->getJson(TENANT_API.'/users?is_active=0')->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Carla');
});

it('creates a tenant user with a role up to the creator level', function () {
    $admin = tenantMember($this->tenant, RoleName::Admin);
    $employee = tenantMember($this->tenant, RoleName::Employee);
    $payload = ['name' => 'Nova', 'email' => 'nova@empresa.com', 'password' => 'password123'];

    $this->actingAs($admin, 'tenant_api')
        ->postJson(TENANT_API.'/users', [...$payload, 'role' => 'employee'])
        ->assertCreated()
        ->assertJsonPath('role', 'employee');

    $this->postJson(TENANT_API.'/users', [...$payload, 'email' => 'outra@empresa.com'])
        ->assertJsonValidationErrors('role');

    // Funcionário não tem users.create
    $this->actingAs($employee, 'tenant_api')
        ->postJson(TENANT_API.'/users', [...$payload, 'email' => 'mais@empresa.com', 'role' => 'employee'])
        ->assertForbidden();
});

it('changes the role of a tenant user but never the own role', function () {
    $admin = tenantMember($this->tenant, RoleName::Admin);
    $employee = tenantMember($this->tenant, RoleName::Employee);

    $this->actingAs($admin, 'tenant_api');

    $this->patchJson(TENANT_API."/users/{$employee->uuid}", ['role' => 'admin'])->assertNoContent();

    $this->tenant->run(function () use ($employee) {
        expect($employee->fresh()->getRoleNames()->all())->toBe(['admin']);
    });

    $this->patchJson(TENANT_API."/users/{$admin->uuid}", ['role' => 'employee'])
        ->assertJsonValidationErrors(['role' => 'Você não pode alterar o seu próprio papel.']);
});

it('returns the authenticated tenant user with role and permissions', function () {
    $employee = tenantMember($this->tenant, RoleName::Employee, ['name' => 'Carla']);

    $this->actingAs($employee, 'tenant_api')
        ->getJson(TENANT_API.'/auth/me')
        ->assertOk()
        ->assertJsonPath('name', 'Carla')
        ->assertJsonPath('role', 'employee')
        ->assertJsonPath('permissions', ['banners.view', 'categories.view', 'products.view', 'users.view', 'visual-identity.view']);
});

it('includes role and permissions in the tenant login response', function () {
    $admin = tenantMember($this->tenant, RoleName::Admin);

    $this->postJson(TENANT_API.'/auth/login', ['email' => $admin->email, 'password' => 'password'])
        ->assertOk()
        ->assertJsonPath('user.role', 'admin')
        ->assertJsonStructure(['user' => ['permissions']]);
});

it('does not reuse the central permission cache inside a tenant', function () {
    // Carrega (e cacheia) as permissions do central antes de entrar no tenant
    $central = User::factory()->create()->assignRole(App\Support\Enums\RoleName::Admin->value);
    expect($central->checkPermissionTo(PermissionName::UsersView))->toBeTrue();

    $admin = tenantMember($this->tenant, RoleName::Admin);

    $this->tenant->run(function () use ($admin) {
        expect($admin->checkPermissionTo(App\Support\Enums\Tenant\PermissionName::UsersView))->toBeTrue();
    });

    expect($central->fresh()->checkPermissionTo(PermissionName::UsersView))->toBeTrue();
});
