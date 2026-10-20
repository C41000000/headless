<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Enums\RoleName;
use Database\Seeders\Central\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function centralUser(?RoleName $role, array $attributes = []): User
{
    $user = User::factory()->create($attributes);

    return $role ? $user->assignRole($role->value) : $user;
}

it('lists users with their role and abilities', function () {
    $admin = centralUser(RoleName::Admin, ['name' => 'Bruno']);
    centralUser(RoleName::SuperAdmin, ['name' => 'Ana']);
    centralUser(RoleName::Employee, ['name' => 'Carla']);

    $this->actingAs($admin, 'api')
        ->getJson('/api/v1/users')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.name', 'Ana')
        ->assertJsonPath('data.0.role', 'super-admin')
        ->assertJsonPath('data.0.abilities', ['update' => false, 'toggle_status' => false, 'delete' => false])
        ->assertJsonPath('data.1.abilities', ['update' => true, 'toggle_status' => false, 'delete' => false])
        ->assertJsonPath('data.2.role', 'employee')
        ->assertJsonPath('data.2.abilities', ['update' => true, 'toggle_status' => true, 'delete' => false]);
});

it('filters users by search, role and status', function () {
    $admin = centralUser(RoleName::Admin, ['name' => 'Bruno']);
    centralUser(RoleName::Employee, ['name' => 'Carla', 'email' => 'carla@loja.com']);
    centralUser(RoleName::Employee, ['name' => 'Diego', 'is_active' => false]);

    $this->actingAs($admin, 'api');

    $this->getJson('/api/v1/users?search=LOJA')->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Carla');
    $this->getJson('/api/v1/users?role=employee')->assertJsonCount(2, 'data');
    $this->getJson('/api/v1/users?is_active=0')->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Diego');
    $this->getJson('/api/v1/users?role=dono')->assertJsonValidationErrors('role');
});

it('requires the users.view permission to list users', function () {
    $this->actingAs(centralUser(null), 'api')->getJson('/api/v1/users')->assertForbidden();
});

it('creates a user with a role up to the creator level', function () {
    $this->actingAs(centralUser(RoleName::Admin), 'api');

    $payload = ['name' => 'Nova', 'email' => 'nova@example.com', 'password' => 'password123'];

    $this->postJson('/api/v1/users', [...$payload, 'role' => 'admin'])
        ->assertCreated()
        ->assertJsonPath('role', 'admin');

    $this->postJson('/api/v1/users', [...$payload, 'email' => 'outra@example.com', 'role' => 'super-admin'])
        ->assertForbidden();

    $this->postJson('/api/v1/users', [...$payload, 'email' => 'mais@example.com'])
        ->assertJsonValidationErrors('role');
});

it('changes the role of a user, replacing the previous one', function () {
    $this->actingAs(centralUser(RoleName::Admin), 'api');
    $employee = centralUser(RoleName::Employee);

    $this->patchJson("/api/v1/users/{$employee->uuid}", ['role' => 'admin'])->assertNoContent();

    expect($employee->fresh()->getRoleNames()->all())->toBe(['admin']);
});

it('does not let an admin promote anyone to super admin', function () {
    $this->actingAs(centralUser(RoleName::Admin), 'api');
    $employee = centralUser(RoleName::Employee);

    $this->patchJson("/api/v1/users/{$employee->uuid}", ['role' => 'super-admin'])->assertForbidden();

    expect($employee->fresh()->hasRole('super-admin'))->toBeFalse();
});

it('does not let an admin demote a super admin', function () {
    $this->actingAs(centralUser(RoleName::Admin), 'api');
    $superAdmin = centralUser(RoleName::SuperAdmin);

    $this->patchJson("/api/v1/users/{$superAdmin->uuid}", ['role' => 'employee'])->assertForbidden();

    expect($superAdmin->fresh()->hasRole('super-admin'))->toBeTrue();
});

it('does not let a user change their own role', function () {
    $superAdmin = centralUser(RoleName::SuperAdmin);
    $this->actingAs($superAdmin, 'api');

    $this->patchJson("/api/v1/users/{$superAdmin->uuid}", ['role' => 'employee'])
        ->assertJsonValidationErrors(['role' => 'Você não pode alterar o seu próprio papel.']);

    // Reenviar o mesmo papel junto com outros campos continua permitido
    $this->patchJson("/api/v1/users/{$superAdmin->uuid}", ['name' => 'Eu', 'role' => 'super-admin'])
        ->assertNoContent();
});

it('returns the authenticated user with role and permissions', function () {
    $this->actingAs(centralUser(RoleName::Employee, ['name' => 'Carla'])->fresh(), 'api')
        ->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('name', 'Carla')
        ->assertJsonPath('role', 'employee')
        ->assertJsonPath('permissions', ['domains.view', 'tenants.view', 'users.view']);
});

it('includes role and permissions in the login response', function () {
    $user = centralUser(RoleName::Admin);

    $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'password'])
        ->assertOk()
        ->assertJsonPath('user.role', 'admin')
        ->assertJsonStructure(['user' => ['permissions']]);
});
