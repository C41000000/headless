---
description: Cria um novo recurso CRUD (rotas, controllers, request, DTO, action, resource, policy, permissions, migration e testes)
---

# Novo recurso CRUD

Antes de começar, pergunte ao usuário (se não estiver claro): contexto **Central ou Tenant**, nome do recurso, campos e tipos, quais roles podem fazer o quê, e se há endpoint público.

Use como referência o módulo Banners. Leia estes arquivos antes de escrever qualquer código:
`app/Http/Controllers/Tenant/Banners/*`, `app/Http/Requests/Tenant/Banners/*`, `app/DTOs/Tenant/Banners/*`, `app/Actions/Tenant/Banners/*`, `app/Http/Resources/Tenant/Banners/BannerResource.php`, `app/Models/Tenant/Banner.php`, `app/Policies/Tenant/BannerPolicy.php`, `tests/Feature/Tenant/BannerTest.php`.

Abaixo, `{Ctx}` = `Central` ou `Tenant`, `{Res}` = recurso no plural (ex.: `Banners`), `{Model}` = singular (ex.: `Banner`).

1. **Migration** em `database/migrations/` (central) ou `database/migrations/tenant/` (tenant): `id`, `uuid` único, colunas, `timestamps`, `softDeletes`.
2. **Model** `app/Models/{Ctx}/{Model}.php`: `#[Fillable]`, `#[UsePolicy]`, `HasFactory`, `HasPublicUuid`, `SoftDeletes`, `casts()`, `$attributes` com defaults, scopes se necessário.
3. **Factory** em `database/factories/{Ctx}/{Model}Factory.php` (+ `newFactory()` no model, se tenant).
4. **Permissions**: adicione `case {Res}View/Create/Update/Delete = '{res}.view'...` em `app/Support/Enums/{Ctx}/PermissionName.php` (central: `app/Support/Enums/PermissionName.php`) e ajuste `RoleName::permissions()`.
5. **Policy** `app/Policies/{Ctx}/{Model}Policy.php` com `viewAny`, `view`, `create`, `update`, `delete` usando `checkPermissionTo`.
6. **FormRequests** `Store{Model}Request` e `Update{Model}Request` (todos os campos com `sometimes`), com PHPDoc + `@example` em cada regra.
7. **DTOs** `Create{Model}DTO` (defaults) e `Update{Model}DTO` (`T|Optional`), sem validação.
8. **Actions** `Create{Model}Action`, `Update{Model}Action`, `Delete{Model}Action` (`final readonly`, `execute()`).
9. **Resource** `{Model}Resource` com `@mixin`, expondo `uuid` (nunca `id`) e PHPDoc por campo.
10. **Controllers** `Index/Show/Store/Update/Delete{Model}Controller`, todos com o mesmo `#[Group(...)]` (escolha um `weight` ainda não usado — busque `weight:` no projeto).
11. **Rotas** em `routes/api.php` ou `routes/tenant.php` dentro do grupo autenticado, com `->name(...)` seguindo o padrão do arquivo.
12. **Testes** em `tests/Feature/{Ctx}/{Model}Test.php` cobrindo 401, 403, CRUD e 422.
13. Execute o workflow `/check` e corrija tudo até passar.
14. Mostre ao usuário um resumo dos arquivos criados e das rotas novas.
