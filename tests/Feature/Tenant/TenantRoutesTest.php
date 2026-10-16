<?php

declare(strict_types=1);

use App\Models\Central\Tenant;
use App\Models\Tenant\User as TenantUser;
use App\Models\User as CentralUser;
use App\Support\Enums\Tenant\RoleName;
use Database\Seeders\Central\RolesAndPermissionsSeeder as CentralRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CentralRolesSeeder::class);
});

function createTestTenant(string $domain = 'empresa.localhost'): Tenant
{
    $tenant = Tenant::query()->create(['name' => 'Empresa Teste']);
    $tenant->domains()->create(['domain' => $domain]);

    return $tenant;
}

it('names the tenant login route correctly', function () {
    expect(route('tenant.v1.auth.login', ['tenant_domain' => 'empresa.localhost'], absolute: false))
        ->toBe('/api/v1/auth/login');
});

it('blocks tenant routes from central domains', function () {
    $this->postJson('http://localhost/api/v1/auth/login', [
        'email' => 'admin@empresa.com',
        'password' => 'password123',
    ])->assertUnprocessable(); // Hit central login route instead of tenant route
});

it('returns 404 on an unknown tenant domain', function () {
    $this->postJson('http://desconhecido.localhost/api/v1/auth/login', [
        'email' => 'alguem@example.com',
        'password' => 'password',
    ])->assertNotFound();
});

it('logs in a tenant user on their tenant domain', function () {
    $tenant = createTestTenant('empresa.localhost');

    $tenant->run(function () {
        TenantUser::factory()->create([
            'email' => 'admin@empresa.com',
            'password' => 'password123',
        ]);
    });

    $this->postJson('http://empresa.localhost/api/v1/auth/login', [
        'email' => 'admin@empresa.com',
        'password' => 'password123',
    ])
        ->assertOk()
        ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'user' => ['uuid', 'email']])
        ->assertJsonPath('token_type', 'Bearer')
        ->assertJsonPath('user.email', 'admin@empresa.com');
});

it('rejects deactivated tenant users from logging in', function () {
    $tenant = createTestTenant('empresa.localhost');

    $tenant->run(function () {
        TenantUser::factory()->create([
            'email' => 'inativo@empresa.com',
            'password' => 'password123',
            'is_active' => false,
        ]);
    });

    $this->postJson('http://empresa.localhost/api/v1/auth/login', [
        'email' => 'inativo@empresa.com',
        'password' => 'password123',
    ])->assertUnprocessable();
});

it('throttles tenant login attempts', function () {
    createTestTenant('empresa.localhost');

    $payload = ['email' => 'alguem@empresa.com', 'password' => 'senha-errada'];

    foreach (range(1, 5) as $attempt) {
        $this->postJson('http://empresa.localhost/api/v1/auth/login', $payload)->assertUnprocessable();
    }

    $this->postJson('http://empresa.localhost/api/v1/auth/login', $payload)->assertTooManyRequests();
});

it('requires authentication to access tenant users routes', function () {
    createTestTenant('empresa.localhost');

    $this->postJson('http://empresa.localhost/api/v1/users', [])->assertUnauthorized();
});

it('rejects central user JWT token on tenant routes', function () {
    createTestTenant('empresa.localhost');
    $centralUser = CentralUser::factory()->create();

    $this->actingAs($centralUser, 'api')
        ->postJson('http://empresa.localhost/api/v1/users', [])
        ->assertUnauthorized();
});

it('requires users.create permission to create users in tenant', function () {
    $tenant = createTestTenant('empresa.localhost');

    $tenantUser = $tenant->run(function () {
        return TenantUser::factory()->create()->assignRole(RoleName::Employee->value);
    });

    $this->actingAs($tenantUser, 'tenant_api')
        ->postJson('http://empresa.localhost/api/v1/users', [
            'name' => 'Novo Funcionário',
            'email' => 'novo@empresa.com',
            'password' => 'password123',
        ])
        ->assertForbidden();
});

it('lets a tenant admin create users in the tenant', function () {
    $tenant = createTestTenant('empresa.localhost');

    $admin = $tenant->run(function () {
        return TenantUser::factory()->create()->assignRole(RoleName::Admin->value);
    });

    $this->actingAs($admin, 'tenant_api')
        ->postJson('http://empresa.localhost/api/v1/users', [
            'name' => 'Novo Funcionário',
            'email' => 'novo@empresa.com',
            'password' => 'password123',
        ])
        ->assertCreated()
        ->assertJsonPath('email', 'novo@empresa.com')
        ->assertJsonPath('name', 'Novo Funcionário');
});
