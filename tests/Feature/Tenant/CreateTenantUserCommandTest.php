<?php

declare(strict_types=1);

use App\Models\Central\Tenant;
use App\Models\Tenant\User as TenantUser;
use App\Support\Enums\Tenant\RoleName;
use Database\Seeders\Central\RolesAndPermissionsSeeder as CentralRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CentralRolesSeeder::class);
});

it('creates a tenant user from options by domain', function () {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    $this->artisan('tenant:user:create', [
        'tenant' => 'empresa.localhost',
        '--name' => 'Carlos Tenant',
        '--email' => 'carlos@empresa.com',
        '--password' => 'senha-segura-123',
        '--role' => RoleName::Admin->value,
    ])
        ->expectsOutputToContain('Usuário carlos@empresa.com criado no tenant Empresa')
        ->assertSuccessful();

    $tenant->run(function () {
        $user = TenantUser::query()->where('email', 'carlos@empresa.com')->sole();

        expect($user->name)->toBe('Carlos Tenant')
            ->and($user->hasRole(RoleName::Admin->value))->toBeTrue()
            ->and($user->is_active)->toBeTrue();
    });
});

it('creates a tenant user from options by tenant uuid', function () {
    $tenant = Tenant::query()->create(['name' => 'Empresa UUID']);
    $tenant->domains()->create(['domain' => 'uuid.localhost']);

    $this->artisan('tenant:user:create', [
        'tenant' => $tenant->uuid,
        '--name' => 'Maria Tenant',
        '--email' => 'maria@uuid.com',
        '--password' => 'senha-segura-123',
        '--role' => RoleName::Employee->value,
    ])
        ->assertSuccessful();

    $tenant->run(function () {
        $user = TenantUser::query()->where('email', 'maria@uuid.com')->sole();

        expect($user->name)->toBe('Maria Tenant')
            ->and($user->hasRole(RoleName::Employee->value))->toBeTrue();
    });
});

it('fails when tenant is not found', function () {
    $this->artisan('tenant:user:create', [
        'tenant' => 'inexistente.localhost',
        '--name' => 'Carlos',
        '--email' => 'carlos@inexistente.com',
        '--password' => 'senha-segura-123',
    ])
        ->expectsOutputToContain('Tenant [inexistente.localhost] não encontrado')
        ->assertFailed();
});

it('asks for missing fields interactively in tenant command', function () {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    $this->artisan('tenant:user:create', [
        'tenant' => 'empresa.localhost',
        '--role' => RoleName::Admin->value,
    ])
        ->expectsQuestion('Nome', 'Maria Souza')
        ->expectsQuestion('E-mail', 'maria@empresa.com')
        ->expectsQuestion('Senha', 'senha-segura-123')
        ->assertSuccessful();

    $tenant->run(function () {
        expect(TenantUser::query()->where('email', 'maria@empresa.com')->exists())->toBeTrue();
    });
});

it('rejects an unknown role in tenant command without creating user', function () {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    $this->artisan('tenant:user:create', [
        'tenant' => 'empresa.localhost',
        '--name' => 'João',
        '--email' => 'joao@empresa.com',
        '--password' => 'senha-segura-123',
        '--role' => 'papel-inexistente',
    ])
        ->assertFailed();

    $tenant->run(function () {
        expect(TenantUser::query()->where('email', 'joao@empresa.com')->exists())->toBeFalse();
    });
});

it('fails without prompting when not interactive in tenant command', function () {
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => 'empresa.localhost']);

    $this->artisan('tenant:user:create', [
        'tenant' => 'empresa.localhost',
        '--no-interaction' => true,
    ])
        ->assertFailed();
});
