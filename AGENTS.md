# AGENTS.md — Guia para agentes de IA

API **headless multi-tenant** em Laravel 13 / PHP 8.5, *database-per-tenant* (stancl/tenancy), servida por Octane + RoadRunner. Não há frontend neste repositório: tudo é JSON.

Leia este arquivo inteiro antes de alterar código. Regras detalhadas por assunto ficam em `.agent/rules/`, e passo a passo de tarefas comuns em `.agent/workflows/`.

---

## Stack

| Item | Uso |
| :--- | :--- |
| `stancl/tenancy` v3 | Um banco por tenant, identificado pelo **domínio** da requisição |
| `tymon/jwt-auth` | Auth JWT. Guards: `api` (central) e `tenant_api` (tenant) |
| `spatie/laravel-permission` | RBAC com roles/permissions definidas em **Backed Enums** |
| `spatie/laravel-data` | DTOs (somente transporte, sem validação) |
| `dedoc/scramble` | OpenAPI gerado a partir de FormRequests, Resources e PHPDoc |
| Pest v5, Larastan (nível 5), Pint (preset laravel + `declare_strict_types`) | Qualidade |
| PostgreSQL 17 / Redis 7 / Horizon | Infra (via Docker) |

## Comandos

Tudo roda dentro do container `app`:

```bash
docker compose exec app php artisan test                        # testes (SQLite em memória)
docker compose exec app php artisan test --filter=BannerTest    # um arquivo/teste
docker compose exec app ./vendor/bin/phpstan analyse --memory-limit=512M
docker compose exec app ./vendor/bin/pint                       # formata
docker compose exec app ./vendor/bin/pint --test                # só valida
```

Antes de dar uma tarefa por concluída: **Pint, PHPStan e testes precisam passar.**

---

## Os dois contextos: Central e Tenant

Todo o código é dividido em dois namespaces espelhados. **Sempre decida primeiro em qual contexto a feature vive.**

| | Central (plataforma) | Tenant (cliente) |
| :--- | :--- | :--- |
| Rotas | `routes/api.php` (prefixo `api/v1`, middleware `EnsureCentralDomain`) | `routes/tenant.php` (`Route::domain('{tenant_domain}')`, prefixo `api/v1`) |
| Nome das rotas | `v1.central.<recurso>.<ação>` | `tenant.v1.<recurso>.<ação>` (painel: `tenant.v1.admin.<recurso>.<ação>`) |
| Guard | `auth:api` | `auth:tenant_api` |
| Models | `App\Models\Central\*` (+ `App\Models\User`) | `App\Models\Tenant\*` |
| Migrations | `database/migrations/` | `database/migrations/tenant/` |
| Seeders | `database/seeders/Central/` | `database/seeders/Tenant/` (chamados por `TenantDatabaseSeeder`) |
| Enums RBAC | `App\Support\Enums\{RoleName,PermissionName}` | `App\Support\Enums\Tenant\{RoleName,PermissionName}` |
| Roles | `super-admin` (3), `admin` (2), `employee` (1) | `admin` (2), `employee` (1) |

O mesmo padrão vale para `Actions/`, `DTOs/`, `Http/Controllers/`, `Http/Requests/`, `Http/Resources/`, `Policies/`: sempre `.../Central/<Recurso>/` ou `.../Tenant/<Recurso>/`.

---

## Arquitetura de um endpoint

Fluxo: **Route → Controller invocável → FormRequest → DTO → Action → Model → Resource**

```
app/Http/Controllers/Tenant/Banners/StoreBannerController.php   # __invoke, Gate::authorize, chama a Action
app/Http/Requests/Tenant/Banners/StoreBannerRequest.php          # TODA a validação + docs dos campos
app/DTOs/Tenant/Banners/CreateBannerDTO.php                      # Data puro, readonly, sem atributos de validação
app/Actions/Tenant/Banners/CreateBannerAction.php                # final readonly, método execute()
app/Http/Resources/Tenant/Banners/BannerResource.php             # formato de saída (expõe uuid, nunca id)
app/Policies/Tenant/BannerPolicy.php                             # checkPermissionTo(PermissionName::...)
```

Use o módulo **Banners** (tenant) e **Tenants/Domains** (central) como referência canônica ao criar algo novo.

### Controllers
- Um controller por ação, `final class XxxController extends Controller`, só `__invoke`.
- Nomes: `Index`, `Show`, `Store`, `Update`, `Delete` + `<Recurso>` + `Controller` (ex.: `ToggleUserStatusController` para ações extras).
- Primeira linha do método: `Gate::authorize('<ability>', $modelOuClasse);`
- Nada de regra de negócio no controller: monte o DTO com `XxxDTO::from($request->validated())` e chame `$action->execute(...)`.
- Atributo Scramble na classe: `#[Group('Tenant - Banners', 'Descrição.', weight: N)]` — reutilize o mesmo group/weight de todos os controllers do recurso.
- PHPDoc do `__invoke` em pt-BR: 1ª linha = título do endpoint, depois descrição, depois `Requer a permission \`recurso.acao\`.`
- Store retorna o Resource com `/** @status 201 */` acima do `return`. Delete retorna `response()->noContent()`.

### FormRequests
- **Toda validação mora aqui.** `authorize()` retorna `true` (autorização é feita pela Policy no controller).
- Cada regra recebe um PHPDoc com descrição e `@example` — é isso que gera a documentação do Scramble.
- Update usa `sometimes` em todos os campos (PATCH parcial); campos anuláveis: `['sometimes', 'nullable', ...]`.
- Mensagens de validação vêm de `lang/` (pt-BR).

### DTOs (`spatie/laravel-data`)
- `final class XxxDTO extends Data` com propriedades `public readonly` promovidas no construtor.
- **Proibido** usar atributos de validação (`#[Required]`, `#[Email]`, `#[Max]`...) — Scramble free não lê laravel-data, e as Actions precisam ser reutilizáveis por comandos CLI.
- DTO de Create: defaults iguais aos do model/migration.
- DTO de Update: tipos `T|Optional` (ou `T|null|Optional`) para que `toArray()` só traga o que foi enviado.
- Datas: `CarbonImmutable`.

### Actions
- `final readonly class XxxAction` com um único método público `execute(...)`.
- Recebem DTO (e o model, em update/delete) e retornam o model (`$model->fresh()` após update).
- Operações com várias escritas: `DB::transaction(...)`. Use `lockForUpdate()` quando houver invariantes concorrentes (ver `DeleteDomainAction`).
- Comandos Artisan que reutilizam uma Action devem validar a entrada antes de montar o DTO (idealmente com as rules do FormRequest).

### Models
- Atributos PHP do Eloquent em vez de propriedades: `#[Fillable([...])]`, `#[Hidden([...])]`, `#[UsePolicy(XxxPolicy::class)]`. **Nunca** `unguard`/`$guarded = []`.
- `use HasPublicUuid` em todo model exposto pela API: PK interna `bigint` (`$table->id()`), coluna `uuid` única usada no Route Model Binding. **Nunca exponha `id` em Resources.**
- `casts()` como método; defaults em `$attributes`; `SoftDeletes` em entidades de negócio.
- Models de tenant com factory em `Database\Factories\Tenant\` precisam de `newFactory()` explícito.
- `Model::shouldBeStrict()` está ativo fora de produção: lazy loading, atributos inexistentes e mass assignment silencioso **lançam exceção**. Use `with()`/`loadMissing()`.

### Resources
- `JsonResource::withoutWrapping()` está ativo: respostas não têm a chave `data` (exceto coleções paginadas).
- `/** @mixin Model */` na classe e PHPDoc com descrição/`@example` em cada campo.

### Policies e RBAC
- Permissions são `recurso.acao` (`banners.view`, `banners.create`, `users.toggle-status`...) definidas como `case` no enum `PermissionName` do contexto.
- Mapeamento role → permissions fica em `RoleName::permissions()`; nível hierárquico em `RoleName::level()`.
- Policies usam `$user->checkPermissionTo(PermissionName::Xxx)` (passando o enum, não string).
- Para usuários: só se gerencia quem tem nível **igual ou menor**, e ninguém exclui/desativa a si mesmo.
- Ao adicionar uma permission: crie o `case`, atualize `RoleName::permissions()` se necessário e rode o seeder correspondente. Os seeders sincronizam e **removem** roles/permissions que não existem mais nos enums.

---

## Convenções gerais

- `declare(strict_types=1);` em **todo** arquivo PHP (Pint exige).
- Classes `final` por padrão (controllers, actions, DTOs, middlewares).
- Comentários, PHPDoc de API, nomes de grupos e mensagens em **português (pt-BR)**; identificadores de código em inglês.
- Comentários explicam o *porquê*, não o *quê*. Mantenha a densidade de comentários do código ao redor.
- Rotas: verbos `get`/`post`/`patch`/`delete` (sem `put`), parâmetros pelo nome do model (`{banner}`), sempre com `->name(...)`.
- Rotas aninhadas no central usam `Route::scopeBindings()` (o filho precisa pertencer ao pai, senão 404).
- Commits: Conventional Commits em pt-BR (`feat: ...`, `fix: ...`).

## Armadilhas conhecidas

- **Octane/RoadRunner**: a aplicação é de longa duração. Não guarde estado de requisição em propriedades estáticas, singletons ou variáveis globais.
- **Tenancy**: dentro do contexto tenant, as queries vão para o banco do tenant. Código que precisa do banco central dentro de uma rota tenant deve usar `tenancy()->central(fn () => ...)`. Em testes/scripts, use `$tenant->run(fn () => ...)`.
- **`{tenant_domain}`** é removido dos parâmetros da rota pelo middleware `StripTenantDomainParameter`; não declare esse argumento nos controllers.
- **Provisionamento do tenant** (create database/migrate/seed) roda após o commit da transação central (`TenancyServiceProvider`). Postgres não roda `CREATE DATABASE` dentro de transação.
- **Soft delete de tenant não apaga o banco** dele.
- Não edite migrations já commitadas: crie uma nova.
- Não altere `.env` nem segredos; use `.env.example` para documentar variáveis novas.

## Testes

- Pest, em `tests/Feature/Central/` e `tests/Feature/Tenant/`. Nomes de teste em inglês: `it('allows a tenant admin to ...')`.
- Banco de teste: SQLite em memória (central) + arquivos SQLite por tenant, limpos no `tearDown` do `TestCase`.
- Detalhes e exemplos em `.agent/rules/testing.md`.
