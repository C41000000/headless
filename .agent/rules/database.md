---
trigger: glob
globs: database/**/*.php
---

# Migrations, factories e seeders

- Central: `database/migrations/`. Tenant: `database/migrations/tenant/` (rodam em cada banco de tenant).
- Nunca altere uma migration já commitada; crie uma nova (`add_xxx_to_yyy_table`).
- Padrão de tabela exposta pela API:

```php
$table->id();                       // PK interna bigint
$table->uuid('uuid')->unique();     // identificador público (HasPublicUuid)
// ... colunas, com ->index() nas usadas em filtros/ordenação
$table->timestamps();
$table->softDeletes();
```

- Strings com tamanho explícito quando houver limite no FormRequest (ex.: URLs `string('x', 2048)`), e defaults iguais aos de `$attributes` do model e do DTO de criação.
- O código precisa funcionar em **PostgreSQL** (produção/dev) e **SQLite** (testes). Evite SQL específico de um banco só.
- Factories de tenant em `database/factories/Tenant/`; o model precisa de `newFactory()` apontando para ela.
- Seeders de tenant ficam em `database/seeders/Tenant/` e entram em `TenantDatabaseSeeder::run()`. Seeders de RBAC são idempotentes (`findOrCreate`, `syncPermissions`) e limpam o cache do `PermissionRegistrar`.
