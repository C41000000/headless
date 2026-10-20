<?php

declare(strict_types=1);

use App\Models\Central\Tenant;
use App\Models\Tenant\User as TenantUser;
use App\Models\Tenant\VisualIdentity;
use App\Support\Enums\Tenant\RoleName;
use Database\Seeders\Central\RolesAndPermissionsSeeder as CentralRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CentralRolesSeeder::class);
});

function createTenantWithDomain(string $domain = 'empresa.localhost'): Tenant
{
    $tenant = Tenant::query()->create(['name' => 'Empresa Teste']);
    $tenant->domains()->create(['domain' => $domain]);

    return $tenant;
}

it('returns default visual identity on public endpoint even if not pre-seeded', function () {
    createTenantWithDomain('empresa.localhost');

    $this->getJson('http://empresa.localhost/api/v1/visual-identity')
        ->assertOk()
        ->assertJsonStructure([
            'uuid',
            'primary_color',
            'secondary_color',
            'accent_color',
            'logo_url',
            'logo_dark_url',
            'favicon_url',
            'font_family',
            'custom_css',
            'updated_at',
        ])
        ->assertJsonPath('primary_color', '#0D6EFD')
        ->assertJsonPath('secondary_color', '#6C757D');
});

it('returns updated visual identity on public endpoint', function () {
    $tenant = createTenantWithDomain('empresa.localhost');

    $tenant->run(function () {
        VisualIdentity::query()->updateOrCreate([], [
            'primary_color' => '#112233',
            'secondary_color' => '#445566',
            'accent_color' => '#FFAA00',
            'font_family' => 'Poppins, sans-serif',
        ]);
    });

    $this->getJson('http://empresa.localhost/api/v1/visual-identity')
        ->assertOk()
        ->assertJsonPath('primary_color', '#112233')
        ->assertJsonPath('secondary_color', '#445566')
        ->assertJsonPath('accent_color', '#FFAA00')
        ->assertJsonPath('font_family', 'Poppins, sans-serif');
});

it('requires authentication to access admin visual identity endpoint', function () {
    createTenantWithDomain('empresa.localhost');

    $this->getJson('http://empresa.localhost/api/v1/admin/visual-identity')
        ->assertUnauthorized();

    $this->patchJson('http://empresa.localhost/api/v1/admin/visual-identity', [
        'primary_color' => '#123456',
    ])->assertUnauthorized();
});

it('denies visual identity update to users without permission', function () {
    $tenant = createTenantWithDomain('empresa.localhost');

    $employee = $tenant->run(function () {
        return TenantUser::factory()->create()->assignRole(RoleName::Employee->value);
    });

    $this->actingAs($employee, 'tenant_api')
        ->patchJson('http://empresa.localhost/api/v1/admin/visual-identity', [
            'primary_color' => '#123456',
        ])
        ->assertForbidden();
});

it('allows tenant admin to view and update visual identity', function () {
    $tenant = createTenantWithDomain('empresa.localhost');

    $admin = $tenant->run(function () {
        return TenantUser::factory()->create()->assignRole(RoleName::Admin->value);
    });

    $this->actingAs($admin, 'tenant_api')
        ->getJson('http://empresa.localhost/api/v1/admin/visual-identity')
        ->assertOk()
        ->assertJsonPath('primary_color', '#0D6EFD');

    $this->actingAs($admin, 'tenant_api')
        ->patchJson('http://empresa.localhost/api/v1/admin/visual-identity', [
            'primary_color' => '#FF5733',
            'secondary_color' => '#33FF57',
            'accent_color' => '#3357FF',
            'logo_url' => 'https://example.com/logo.png',
            'logo_dark_url' => 'https://example.com/logo-dark.png',
            'favicon_url' => 'https://example.com/favicon.ico',
            'font_family' => 'Roboto, sans-serif',
            'custom_css' => 'body { margin: 0; }',
        ])
        ->assertOk()
        ->assertJsonPath('primary_color', '#FF5733')
        ->assertJsonPath('secondary_color', '#33FF57')
        ->assertJsonPath('accent_color', '#3357FF')
        ->assertJsonPath('logo_url', 'https://example.com/logo.png')
        ->assertJsonPath('font_family', 'Roboto, sans-serif');

    $tenant->run(function () {
        $saved = VisualIdentity::query()->sole();
        expect($saved->primary_color)->toBe('#FF5733')
            ->and($saved->logo_url)->toBe('https://example.com/logo.png');
    });
});

it('validates hex color format and URLs', function () {
    $tenant = createTenantWithDomain('empresa.localhost');

    $admin = $tenant->run(function () {
        return TenantUser::factory()->create()->assignRole(RoleName::Admin->value);
    });

    $this->actingAs($admin, 'tenant_api')
        ->patchJson('http://empresa.localhost/api/v1/admin/visual-identity', [
            'primary_color' => 'blue', // invalid hex
            'logo_url' => 'not-a-valid-url',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['primary_color', 'logo_url']);
});
