---
trigger: glob
globs: tests/**/*.php
---

# Testes (Pest v5)

- Feature tests em `tests/Feature/Central/` ou `tests/Feature/Tenant/`, um arquivo por recurso (`BannerTest.php`).
- Estilo funcional do Pest: `uses(RefreshDatabase::class);`, `beforeEach(...)`, `it('...', function () { ... })`. Descrições em inglês.
- Testes rodam com SQLite em memória (`phpunit.xml`). O `TestCase` aborta se o banco não for de teste e apaga os arquivos `database/tenant*` no `tearDown`.

## Testes de rotas tenant

```php
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CentralRolesSeeder::class);
});

function createXxxTenant(string $domain = 'empresa.localhost'): Tenant
{
    $tenant = Tenant::query()->create(['name' => 'Empresa']);
    $tenant->domains()->create(['domain' => $domain]);

    return $tenant;
}

it('allows a tenant admin to create xxx', function () {
    $tenant = createXxxTenant();

    // Dados do tenant são criados DENTRO do contexto dele
    $admin = $tenant->run(fn () => TenantUser::factory()->create()->assignRole(RoleName::Admin->value));

    $this->actingAs($admin, 'tenant_api')
        ->postJson('http://empresa.localhost/api/v1/admin/xxx', [/* ... */])
        ->assertCreated();
});
```

- Criar um tenant dispara o provisionamento do banco + seed (roles/permissions do tenant já existem).
- Requisições tenant usam URL absoluta com o domínio do tenant (`http://empresa.localhost/api/v1/...`).
- Funções helper no topo do arquivo precisam de nome único no projeto inteiro (Pest carrega todos os arquivos no mesmo escopo global): prefixe com o recurso, ex.: `createBannerTenant()`.
- Leituras/asserções no banco do tenant: `$tenant->run(fn () => ...)`.

## Testes de rotas central

- `actingAs($user, 'api')` e URLs relativas (`/api/v1/...`).

## O que cobrir em todo recurso novo

1. 401 sem autenticação.
2. 403 para a role sem permissão (ex.: `employee` tentando criar/editar/excluir).
3. CRUD completo para quem tem permissão (status 201 / 200 / 204, soft delete, 404 depois de excluir).
4. 422 com `assertJsonValidationErrors([...])` para payload inválido.
5. Regras de hierarquia/autoexclusão quando o recurso envolve usuários.
