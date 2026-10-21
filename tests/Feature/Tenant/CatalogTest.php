<?php

declare(strict_types=1);

use App\Models\Central\Tenant;
use App\Models\Tenant\Category;
use App\Models\Tenant\Product;
use App\Models\Tenant\User as TenantUser;
use App\Support\Enums\Tenant\RoleName;
use Database\Seeders\Central\RolesAndPermissionsSeeder as CentralRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CentralRolesSeeder::class);
});

function createCatalogTenant(string $domain = 'loja.localhost'): Tenant
{
    $tenant = Tenant::query()->create(['name' => 'Loja']);
    $tenant->domains()->create(['domain' => $domain]);

    return $tenant;
}

it('returns only active categories in order on public endpoint', function () {
    $tenant = createCatalogTenant('loja.localhost');

    $tenant->run(function () {
        Category::factory()->create([
            'name' => 'Camisetas',
            'order' => 2,
            'is_active' => true,
        ]);

        Category::factory()->create([
            'name' => 'Acessórios',
            'order' => 1,
            'is_active' => true,
        ]);

        Category::factory()->create([
            'name' => 'Inativos',
            'is_active' => false,
        ]);
    });

    $response = $this->getJson('http://loja.localhost/api/v1/categories')
        ->assertOk()
        ->assertJsonCount(2);

    expect($response->json('0.name'))->toBe('Acessórios')
        ->and($response->json('1.name'))->toBe('Camisetas');
});

it('returns only active products with filters on public endpoint', function () {
    $tenant = createCatalogTenant('loja.localhost');

    $tenant->run(function () {
        $cat1 = Category::factory()->create(['name' => 'Calçados', 'slug' => 'calcados']);
        $cat2 = Category::factory()->create(['name' => 'Roupas', 'slug' => 'roupas']);

        Product::factory()->create([
            'category_id' => $cat1->id,
            'name' => 'Tênis Esportivo',
            'slug' => 'tenis-esportivo',
            'is_active' => true,
            'is_featured' => true,
        ]);

        Product::factory()->create([
            'category_id' => $cat2->id,
            'name' => 'Camisa Social',
            'slug' => 'camisa-social',
            'is_active' => true,
            'is_featured' => false,
        ]);

        Product::factory()->create([
            'category_id' => $cat1->id,
            'name' => 'Sandália Inativa',
            'slug' => 'sandalia-inativa',
            'is_active' => false,
        ]);
    });

    // Listagem geral pública (deve trazer 2 produtos ativos)
    $this->getJson('http://loja.localhost/api/v1/products')
        ->assertOk()
        ->assertJsonCount(2, 'data');

    // Filtro por destaque
    $this->getJson('http://loja.localhost/api/v1/products?featured=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'tenis-esportivo');

    // Filtro por categoria
    $this->getJson('http://loja.localhost/api/v1/products?category_slug=roupas')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'camisa-social');

    // Busca por termo
    $this->getJson('http://loja.localhost/api/v1/products?search=Tênis')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Tênis Esportivo');
});

it('returns product details by slug on public endpoint', function () {
    $tenant = createCatalogTenant('loja.localhost');

    $tenant->run(function () {
        $cat = Category::factory()->create(['name' => 'Eletrônicos', 'slug' => 'eletronicos']);
        Product::factory()->create([
            'category_id' => $cat->id,
            'name' => 'Mouse Gamer',
            'slug' => 'mouse-gamer',
            'is_active' => true,
        ]);
        Product::factory()->create([
            'category_id' => $cat->id,
            'name' => 'Teclado Inativo',
            'slug' => 'teclado-inativo',
            'is_active' => false,
        ]);
    });

    $this->getJson('http://loja.localhost/api/v1/products/mouse-gamer')
        ->assertOk()
        ->assertJsonPath('name', 'Mouse Gamer')
        ->assertJsonPath('category.name', 'Eletrônicos');

    $this->getJson('http://loja.localhost/api/v1/products/teclado-inativo')
        ->assertNotFound();
});

it('requires authentication to access admin catalog endpoints', function () {
    createCatalogTenant('loja.localhost');

    $this->getJson('http://loja.localhost/api/v1/admin/categories')->assertUnauthorized();
    $this->postJson('http://loja.localhost/api/v1/admin/categories', [])->assertUnauthorized();
    $this->getJson('http://loja.localhost/api/v1/admin/products')->assertUnauthorized();
    $this->postJson('http://loja.localhost/api/v1/admin/products', [])->assertUnauthorized();
});

it('allows an employee to view but not create, update, or delete categories and products', function () {
    $tenant = createCatalogTenant('loja.localhost');

    [$employee, $category, $product] = $tenant->run(function () {
        $cat = Category::factory()->create(['name' => 'Livros']);
        $prod = Product::factory()->create(['category_id' => $cat->id, 'name' => 'Livro Laravel']);
        $emp = TenantUser::factory()->create()->assignRole(RoleName::Employee->value);

        return [$emp, $cat, $prod];
    });

    $headers = [
        'Authorization' => 'Bearer '.auth('tenant_api')->login($employee),
    ];

    // Permite leitura
    $this->getJson('http://loja.localhost/api/v1/admin/categories', $headers)->assertOk();
    $this->getJson("http://loja.localhost/api/v1/admin/categories/{$category->uuid}", $headers)->assertOk();
    $this->getJson('http://loja.localhost/api/v1/admin/products', $headers)->assertOk();
    $this->getJson("http://loja.localhost/api/v1/admin/products/{$product->uuid}", $headers)->assertOk();

    // Bloqueia escrita
    $this->postJson('http://loja.localhost/api/v1/admin/categories', ['name' => 'Novo'], $headers)->assertForbidden();
    $this->patchJson("http://loja.localhost/api/v1/admin/categories/{$category->uuid}", ['name' => 'Novo'], $headers)->assertForbidden();
    $this->deleteJson("http://loja.localhost/api/v1/admin/categories/{$category->uuid}", [], $headers)->assertForbidden();

    $this->postJson('http://loja.localhost/api/v1/admin/products', ['name' => 'Novo', 'price' => 10], $headers)->assertForbidden();
    $this->patchJson("http://loja.localhost/api/v1/admin/products/{$product->uuid}", ['name' => 'Novo'], $headers)->assertForbidden();
    $this->deleteJson("http://loja.localhost/api/v1/admin/products/{$product->uuid}", [], $headers)->assertForbidden();
});

it('allows a tenant admin to perform full CRUD on categories and products', function () {
    $tenant = createCatalogTenant('loja.localhost');

    $admin = $tenant->run(function () {
        return TenantUser::factory()->create()->assignRole(RoleName::Admin->value);
    });

    $headers = [
        'Authorization' => 'Bearer '.auth('tenant_api')->login($admin),
    ];

    // 1. Criar Categoria
    $categoryResponse = $this->postJson('http://loja.localhost/api/v1/admin/categories', [
        'name' => 'Hardware',
        'description' => 'Peças de computador',
        'is_active' => true,
        'order' => 1,
    ], $headers)
        ->assertCreated()
        ->assertJsonPath('name', 'Hardware')
        ->assertJsonPath('slug', 'hardware');

    $categoryUuid = $categoryResponse->json('uuid');

    // 2. Atualizar Categoria
    $this->patchJson("http://loja.localhost/api/v1/admin/categories/{$categoryUuid}", [
        'name' => 'Hardware & Peças',
    ], $headers)
        ->assertOk()
        ->assertJsonPath('name', 'Hardware & Peças');

    // 3. Criar Produto vinculado à categoria
    $productResponse = $this->postJson('http://loja.localhost/api/v1/admin/products', [
        'name' => 'Placa de Vídeo RTX 4080',
        'price' => 7500.00,
        'promotional_price' => 6999.90,
        'stock' => 15,
        'sku' => 'RTX-4080-16GB',
        'category_uuid' => $categoryUuid,
        'is_active' => true,
        'is_featured' => true,
    ], $headers)
        ->assertCreated()
        ->assertJsonPath('name', 'Placa de Vídeo RTX 4080')
        ->assertJsonPath('slug', 'placa-de-video-rtx-4080')
        ->assertJsonPath('category_uuid', $categoryUuid);

    $productUuid = $productResponse->json('uuid');

    // 4. Atualizar Produto
    $this->patchJson("http://loja.localhost/api/v1/admin/products/{$productUuid}", [
        'price' => 7200.00,
        'promotional_price' => 6500.00,
    ], $headers)->assertOk();

    expect((float) $this->getJson("http://loja.localhost/api/v1/admin/products/{$productUuid}", $headers)->json('price'))->toBe(7200.0)
        ->and((float) $this->getJson("http://loja.localhost/api/v1/admin/products/{$productUuid}", $headers)->json('promotional_price'))->toBe(6500.0);

    // 5. Excluir Produto e Categoria (soft delete)
    $this->deleteJson("http://loja.localhost/api/v1/admin/products/{$productUuid}", [], $headers)
        ->assertNoContent();

    $this->deleteJson("http://loja.localhost/api/v1/admin/categories/{$categoryUuid}", [], $headers)
        ->assertNoContent();

    $tenant->run(function () use ($categoryUuid, $productUuid) {
        expect(Category::where('uuid', $categoryUuid)->exists())->toBeFalse()
            ->and(Category::withTrashed()->where('uuid', $categoryUuid)->exists())->toBeTrue()
            ->and(Product::where('uuid', $productUuid)->exists())->toBeFalse()
            ->and(Product::withTrashed()->where('uuid', $productUuid)->exists())->toBeTrue();
    });
});

it('validates product price and promotional price rules', function () {
    $tenant = createCatalogTenant('loja.localhost');

    $admin = $tenant->run(function () {
        return TenantUser::factory()->create()->assignRole(RoleName::Admin->value);
    });

    $headers = [
        'Authorization' => 'Bearer '.auth('tenant_api')->login($admin),
    ];

    // Preço promocional maior que o preço normal deve falhar
    $this->postJson('http://loja.localhost/api/v1/admin/products', [
        'name' => 'Headset Gamer',
        'price' => 200.00,
        'promotional_price' => 250.00,
    ], $headers)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('promotional_price');
});
