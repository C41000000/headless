---
trigger: always_on
---

# Regras essenciais do projeto

O guia completo está em `AGENTS.md` na raiz. Leia-o antes de qualquer alteração de código.

Regras inegociáveis:

1. **Decida o contexto primeiro**: Central (`routes/api.php`, guard `api`) ou Tenant (`routes/tenant.php`, guard `auth:tenant_api`). Todos os diretórios (`Actions`, `DTOs`, `Http/*`, `Models`, `Policies`, enums) são espelhados em `Central/` e `Tenant/`.
2. **Fluxo de endpoint**: Controller invocável (`final`, só `__invoke`) → `Gate::authorize()` → FormRequest → `XxxDTO::from($request->validated())` → `XxxAction->execute()` → Resource.
3. **Validação só no FormRequest.** DTOs (`spatie/laravel-data`) não levam atributos de validação.
4. **Models** usam `#[Fillable]`, `#[Hidden]`, `#[UsePolicy]` e `HasPublicUuid`. A API expõe `uuid`, nunca `id`.
5. **Permissions** vêm dos enums `PermissionName`/`RoleName` do contexto; policies usam `checkPermissionTo(PermissionName::Xxx)`.
6. `declare(strict_types=1);` em todo arquivo PHP. Comentários e docs de API em pt-BR.
7. Imite o módulo **Banners** (`app/**/Tenant/Banners/`) ao criar recursos novos — leia os arquivos dele antes de escrever.
8. Antes de concluir: rode Pint, PHPStan e os testes (workflow `/check`). Não diga que passou sem ter rodado.
9. Não edite migrations já commitadas, `.env` ou `vendor/`.
