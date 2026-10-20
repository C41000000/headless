<?php

declare(strict_types=1);

use App\Models\Central\Tenant;
use App\Models\Tenant\Banner;
use App\Models\Tenant\User as TenantUser;
use App\Support\Enums\Tenant\RoleName;
use Database\Seeders\Central\RolesAndPermissionsSeeder as CentralRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CentralRolesSeeder::class);
});

function createBannerTenant(string $domain = 'empresa.localhost'): Tenant
{
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => $domain]);

    return $tenant;
}

it('returns only active and scheduled banners on public endpoint', function () {
    $tenant = createBannerTenant('empresa.localhost');

    $tenant->run(function () {
        Banner::factory()->create([
            'title' => 'Banner Ativo 1',
            'order' => 2,
            'placement' => 'home',
            'is_active' => true,
        ]);

        Banner::factory()->create([
            'title' => 'Banner Ativo 2 (Primeiro)',
            'order' => 1,
            'placement' => 'home',
            'is_active' => true,
        ]);

        Banner::factory()->create([
            'title' => 'Banner Inativo',
            'is_active' => false,
        ]);

        Banner::factory()->create([
            'title' => 'Banner Expirado',
            'is_active' => true,
            'ends_at' => now()->subDay(),
        ]);

        Banner::factory()->create([
            'title' => 'Banner Futuro',
            'is_active' => true,
            'starts_at' => now()->addDay(),
        ]);
    });

    $response = $this->getJson('http://empresa.localhost/api/v1/banners?placement=home')
        ->assertOk()
        ->assertJsonCount(2);

    expect($response->json('0.title'))->toBe('Banner Ativo 2 (Primeiro)')
        ->and($response->json('1.title'))->toBe('Banner Ativo 1');
});

it('filters public banners by placement', function () {
    $tenant = createBannerTenant('empresa.localhost');

    $tenant->run(function () {
        Banner::factory()->create(['title' => 'Home Banner', 'placement' => 'home', 'is_active' => true]);
        Banner::factory()->create(['title' => 'Sidebar Banner', 'placement' => 'sidebar', 'is_active' => true]);
    });

    $this->getJson('http://empresa.localhost/api/v1/banners?placement=sidebar')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.title', 'Sidebar Banner');
});

it('requires authentication to access admin banners endpoints', function () {
    createBannerTenant('empresa.localhost');

    $this->getJson('http://empresa.localhost/api/v1/admin/banners')->assertUnauthorized();
    $this->postJson('http://empresa.localhost/api/v1/admin/banners', [])->assertUnauthorized();
});

it('allows an employee to view but not create, update, or delete banners', function () {
    $tenant = createBannerTenant('empresa.localhost');

    [$employee, $banner] = $tenant->run(function () {
        return [
            TenantUser::factory()->create()->assignRole(RoleName::Employee->value),
            Banner::factory()->create(['title' => 'Banner Original']),
        ];
    });

    $this->actingAs($employee, 'tenant_api')
        ->getJson('http://empresa.localhost/api/v1/admin/banners')
        ->assertOk()
        ->assertJsonCount(1);

    $this->actingAs($employee, 'tenant_api')
        ->getJson("http://empresa.localhost/api/v1/admin/banners/{$banner->uuid}")
        ->assertOk()
        ->assertJsonPath('title', 'Banner Original');

    $this->actingAs($employee, 'tenant_api')
        ->postJson('http://empresa.localhost/api/v1/admin/banners', [
            'image_url' => 'https://example.com/banner.png',
        ])
        ->assertForbidden();

    $this->actingAs($employee, 'tenant_api')
        ->patchJson("http://empresa.localhost/api/v1/admin/banners/{$banner->uuid}", [
            'title' => 'Tentativa de Edição',
        ])
        ->assertForbidden();

    $this->actingAs($employee, 'tenant_api')
        ->deleteJson("http://empresa.localhost/api/v1/admin/banners/{$banner->uuid}")
        ->assertForbidden();
});

it('allows a tenant admin to perform full CRUD on banners', function () {
    $tenant = createBannerTenant('empresa.localhost');

    $admin = $tenant->run(function () {
        return TenantUser::factory()->create()->assignRole(RoleName::Admin->value);
    });

    // 1. Create
    $createResponse = $this->actingAs($admin, 'tenant_api')
        ->postJson('http://empresa.localhost/api/v1/admin/banners', [
            'title' => 'Campanha Black Friday',
            'subtitle' => 'Até 70% OFF',
            'image_url' => 'https://example.com/banner.webp',
            'mobile_image_url' => 'https://example.com/banner-mob.webp',
            'link_url' => 'https://empresa.com/black-friday',
            'target' => '_blank',
            'placement' => 'home',
            'order' => 1,
            'is_active' => true,
        ])
        ->assertCreated()
        ->assertJsonPath('title', 'Campanha Black Friday')
        ->assertJsonPath('target', '_blank')
        ->assertJsonPath('placement', 'home');

    $uuid = $createResponse->json('uuid');

    // 2. Read (Show)
    $this->actingAs($admin, 'tenant_api')
        ->getJson("http://empresa.localhost/api/v1/admin/banners/{$uuid}")
        ->assertOk()
        ->assertJsonPath('uuid', $uuid)
        ->assertJsonPath('title', 'Campanha Black Friday');

    // 3. Update
    $this->actingAs($admin, 'tenant_api')
        ->patchJson("http://empresa.localhost/api/v1/admin/banners/{$uuid}", [
            'title' => 'Campanha Black Friday Prorrogada',
            'order' => 5,
        ])
        ->assertOk()
        ->assertJsonPath('title', 'Campanha Black Friday Prorrogada')
        ->assertJsonPath('order', 5);

    // 4. Delete (Soft delete)
    $this->actingAs($admin, 'tenant_api')
        ->deleteJson("http://empresa.localhost/api/v1/admin/banners/{$uuid}")
        ->assertNoContent();

    $tenant->run(function () use ($uuid) {
        $banner = Banner::withTrashed()->where('uuid', $uuid)->firstOrFail();
        expect($banner->trashed())->toBeTrue();
    });

    // 5. Subsequent show returns 404
    $this->actingAs($admin, 'tenant_api')
        ->getJson("http://empresa.localhost/api/v1/admin/banners/{$uuid}")
        ->assertNotFound();
});

it('validates banner payload when creating', function () {
    $tenant = createBannerTenant('empresa.localhost');

    $admin = $tenant->run(function () {
        return TenantUser::factory()->create()->assignRole(RoleName::Admin->value);
    });

    $this->actingAs($admin, 'tenant_api')
        ->postJson('http://empresa.localhost/api/v1/admin/banners', [
            'image_url' => 'not-a-valid-url',
            'target' => 'invalid-target',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['image_url', 'target']);
});
