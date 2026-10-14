<?php

declare(strict_types=1);

use App\Models\Central\Domain;
use App\Support\Enums\RoleName;
use Database\Seeders\Central\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Stancl\Tenancy\Events\TenantCreated;

uses(RefreshDatabase::class);

beforeEach(function () {
    Event::fake([TenantCreated::class]);

    $this->seed(RolesAndPermissionsSeeder::class);
});

it('adds, lists, shows and updates domains of a tenant', function () {
    actingAsRole(RoleName::Admin);
    $tenant = tenantWithDomains('a.app.com');
    $base = "/api/v1/tenants/{$tenant->uuid}/domains";

    $uuid = $this->postJson($base, ['domain' => 'B.App.com'])
        ->assertCreated()
        ->assertJsonPath('domain', 'b.app.com')
        ->json('uuid');

    $this->getJson($base)->assertOk()->assertJsonCount(2);
    $this->getJson("{$base}/{$uuid}")->assertOk()->assertJsonPath('domain', 'b.app.com');
    $this->patchJson("{$base}/{$uuid}", ['domain' => 'c.app.com'])->assertNoContent();

    expect(Domain::query()->where('uuid', $uuid)->value('domain'))->toBe('c.app.com');
});

it('allows keeping the same domain on update', function () {
    actingAsRole(RoleName::Admin);
    $tenant = tenantWithDomains('a.app.com');
    $domain = $tenant->domains()->sole();

    $this->patchJson("/api/v1/tenants/{$tenant->uuid}/domains/{$domain->uuid}", ['domain' => 'a.app.com'])
        ->assertNoContent();
});

it('rejects a domain already used by another tenant', function () {
    actingAsRole(RoleName::Admin);
    tenantWithDomains('a.app.com');
    $tenant = tenantWithDomains('b.app.com');

    $this->postJson("/api/v1/tenants/{$tenant->uuid}/domains", ['domain' => 'a.app.com'])
        ->assertJsonValidationErrors('domain');
});

it('returns 404 for a domain of another tenant', function () {
    actingAsRole(RoleName::SuperAdmin);
    $tenant = tenantWithDomains('a.app.com');
    $other = tenantWithDomains('b.app.com', 'c.app.com')->domains()->first();

    $this->getJson("/api/v1/tenants/{$tenant->uuid}/domains/{$other->uuid}")->assertNotFound();
    $this->deleteJson("/api/v1/tenants/{$tenant->uuid}/domains/{$other->uuid}")->assertNotFound();
});

it('deletes a domain but never the last one', function () {
    actingAsRole(RoleName::SuperAdmin);
    $tenant = tenantWithDomains('a.app.com', 'b.app.com');
    [$first, $second] = $tenant->domains()->orderBy('domain')->get();

    $this->deleteJson("/api/v1/tenants/{$tenant->uuid}/domains/{$first->uuid}")->assertNoContent();
    $this->deleteJson("/api/v1/tenants/{$tenant->uuid}/domains/{$second->uuid}")->assertJsonValidationErrors('domain');

    expect($tenant->domains()->pluck('domain')->all())->toBe(['b.app.com']);
});

it('enforces domain permissions', function (RoleName $role, string $method, int $status) {
    actingAsRole($role);
    $tenant = tenantWithDomains('a.app.com', 'b.app.com');
    $domain = $tenant->domains()->first();

    $uri = $method === 'POST'
        ? "/api/v1/tenants/{$tenant->uuid}/domains"
        : "/api/v1/tenants/{$tenant->uuid}/domains/{$domain->uuid}";

    $this->json($method, $uri, ['domain' => 'novo.app.com'])->assertStatus($status);
})->with([
    'employee cria' => [RoleName::Employee, 'POST', 403],
    'employee edita' => [RoleName::Employee, 'PATCH', 403],
    'admin exclui' => [RoleName::Admin, 'DELETE', 403],
    'employee vê' => [RoleName::Employee, 'GET', 200],
]);
