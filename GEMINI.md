# GEMINI.md

As instruções deste projeto estão em **`AGENTS.md`** (raiz). Leia-o inteiro antes de alterar código.

Complementos:

- `.agent/rules/` — regras por assunto (`core.md` sempre ativa; `testing.md` para `tests/**`; `database.md` para `database/**`).
- `.agent/workflows/` — passo a passo: `/new-resource` (novo CRUD) e `/check` (Pint + PHPStan + testes).

Resumo do que não pode ser esquecido:

1. Decida o contexto primeiro: **Central** (`routes/api.php`, guard `api`) ou **Tenant** (`routes/tenant.php`, guard `tenant_api`).
2. Controller invocável → `Gate::authorize()` → FormRequest → `DTO::from($request->validated())` → Action → Resource.
3. Validação só no FormRequest; DTOs sem atributos de validação.
4. A API expõe `uuid`, nunca `id`.
5. Use o módulo Banners como referência e rode `/check` antes de concluir.
