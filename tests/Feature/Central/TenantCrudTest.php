<?php

declare(strict_types=1);

use App\Models\Central\Domain;
use App\Models\Central\Tenant;
use App\Models\User;
use App\Support\Enums\RoleName;
use Database\Seeders\Central\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Sem provisionar banco de verdade (no SQLite viraria arquivo em database/)
    Event::fake([TenantCreated::class]);

    $this->seed(RolesAndPermissionsSeeder::class);
});

function actingAsRole(RoleName $role): User
{
    $user = User::factory()->create()->assignRole($role->value);
    test()->actingAs($user, 'api');

    return $user;
}

function tenantWithDomains(string ...$domains): Tenant
{
    $tenant = Tenant::query()->create(['name' => 'Cliente']);

    foreach ($domains as $domain) {
        $tenant->domains()->create(['domain' => $domain]);
    }

    return $tenant;
}

it('creates a tenant with its domains', function () {
    actingAsRole(RoleName::Admin);

    $this->postJson('/api/v1/tenants', [
        'name' => 'Cliente X',
        'domains' => ['ClienteX.App.com ', 'www.clientex.app.com'],
    ])
        ->assertCreated()
        ->assertJsonPath('name', 'Cliente X')
        ->assertJsonPath('domains.0.domain', 'clientex.app.com')
        ->assertJsonCount(2, 'domains');

    $tenant = Tenant::query()->sole();

    expect($tenant->created_at)->not->toBeNull()
        ->and($tenant->getAttributes())->toHaveKey('created_at');

    Event::assertDispatched(TenantCreated::class);
});

it('validates the tenant payload', function (array $payload, string $errorKey) {
    actingAsRole(RoleName::SuperAdmin);
    config()->set('tenancy.central_domains', ['localhost', 'api.app.com']);
    tenantWithDomains('em-uso.app.com');

    $this->postJson('/api/v1/tenants', $payload)->assertJsonValidationErrors($errorKey);
})->with([
    'sem nome' => [['domains' => ['a.app.com']], 'name'],
    'sem domínios' => [['name' => 'X', 'domains' => []], 'domains'],
    'domínio inválido' => [['name' => 'X', 'domains' => ['nao é domínio']], 'domains.0'],
    'domínio central' => [['name' => 'X', 'domains' => ['api.app.com']], 'domains.0'],
    'domínio em uso' => [['name' => 'X', 'domains' => ['em-uso.app.com']], 'domains.0'],
    'domínio repetido' => [['name' => 'X', 'domains' => ['a.app.com', 'A.app.com']], 'domains.0'],
]);

it('removes the tenant when provisioning fails, freeing its domains', function () {
    actingAsRole(RoleName::SuperAdmin);

    // Desfaz o Event::fake do beforeEach para o listener abaixo rodar de verdade
    $dispatcher = Event::getFacadeRoot()->dispatcher;
    Event::swap($dispatcher);
    Model::setEventDispatcher($dispatcher);

    Event::forget(TenantCreated::class);
    Event::listen(TenantCreated::class, fn () => DB::afterCommit(fn () => throw new RuntimeException('falha ao criar banco')));

    $this->withoutExceptionHandling();

    expect(fn () => $this->postJson('/api/v1/tenants', ['name' => 'X', 'domains' => ['x.app.com']]))
        ->toThrow(RuntimeException::class, 'falha ao criar banco');

    expect(Tenant::withTrashed()->count())->toBe(0)
        ->and(Domain::query()->count())->toBe(0);
});

it('lists tenants paginated with their domains', function () {
    actingAsRole(RoleName::Employee);
    tenantWithDomains('a.app.com');
    tenantWithDomains('b.app.com');

    $this->getJson('/api/v1/tenants')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonStructure(['data' => [['uuid', 'name', 'domains' => [['uuid', 'domain']]]], 'meta', 'links']);
});

it('filters tenants by name or domain', function () {
    actingAsRole(RoleName::Employee);
    tenantWithDomains('a.app.com')->update(['name' => 'Gama Ltda.']);
    tenantWithDomains('loja-beta.app.com')->update(['name' => 'Beta']);
    tenantWithDomains('c.app.com')->update(['name' => 'Delta']);

    $this->getJson('/api/v1/tenants?search=gama')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Gama Ltda.');

    $this->getJson('/api/v1/tenants?search=LOJA-')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Beta')
        ->assertJsonPath('meta.total', 1);

    $this->getJson('/api/v1/tenants?search=')->assertOk()->assertJsonCount(3, 'data');
});

it('shows and updates a tenant', function () {
    actingAsRole(RoleName::Admin);
    $tenant = tenantWithDomains('a.app.com');

    $this->getJson("/api/v1/tenants/{$tenant->uuid}")->assertOk()->assertJsonPath('domains.0.domain', 'a.app.com');
    $this->patchJson("/api/v1/tenants/{$tenant->uuid}", ['name' => 'Novo nome'])->assertNoContent();

    expect($tenant->fresh()->name)->toBe('Novo nome');
});

it('soft deletes a tenant, keeping its domains for a restore', function () {
    actingAsRole(RoleName::SuperAdmin);
    $tenant = tenantWithDomains('a.app.com');

    $this->deleteJson("/api/v1/tenants/{$tenant->uuid}")->assertNoContent();

    $this->assertSoftDeleted($tenant);
    expect($tenant->domains()->count())->toBe(1);

    $this->getJson("/api/v1/tenants/{$tenant->uuid}")->assertNotFound();
});

it('enforces tenant permissions', function (RoleName $role, string $method, string $uri, int $status) {
    actingAsRole($role);
    $tenant = tenantWithDomains('a.app.com');

    $this->json($method, str_replace('{tenant}', $tenant->uuid, $uri), ['name' => 'X', 'domains' => ['novo.app.com']])
        ->assertStatus($status);
})->with([
    'employee cria' => [RoleName::Employee, 'POST', '/api/v1/tenants', 403],
    'employee edita' => [RoleName::Employee, 'PATCH', '/api/v1/tenants/{tenant}', 403],
    'employee exclui' => [RoleName::Employee, 'DELETE', '/api/v1/tenants/{tenant}', 403],
    'admin exclui' => [RoleName::Admin, 'DELETE', '/api/v1/tenants/{tenant}', 403],
    'employee vê' => [RoleName::Employee, 'GET', '/api/v1/tenants/{tenant}', 200],
]);
