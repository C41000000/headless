# Headless Multi-Tenant API

API RESTful headless desenvolvida para gerenciamento central da plataforma e provisionamento de múltiplos tenants (arquitetura *database-per-tenant*). Projetada para alta concorrência e baixa latência utilizando **Laravel Octane** com **RoadRunner**, **PostgreSQL 17** e **Redis 7**.

---

## Tecnologias e Stack

* **Runtime & Framework:** PHP 8.5+ e Laravel 12+
* **Servidor de Alta Performance:** [Laravel Octane](https://laravel.com/docs/octane) + [RoadRunner](https://roadrunner.dev/) (com hot reload via Chokidar em ambiente de desenvolvimento)
* **Bancos de Dados:** [PostgreSQL 17](https://www.postgresql.org/) (conexões separadas para banco central e bancos tenant)
* **Filas, Cache e Locks Distribuídos:** [Redis 7](https://redis.io/) + [Laravel Horizon](https://laravel.com/docs/horizon)
* **Multi-Tenancy:** [stancl/tenancy](https://tenancyforlaravel.com/) (provisionamento dinâmico de banco por tenant)
* **Autenticação:** JWT ([tymon/jwt-auth](https://jwt-auth.readthedocs.io/))
* **Autorização (RBAC):** [spatie/laravel-permission](https://spatie.be/docs/laravel-permission) com Backed Enums e hierarquia de cargos
* **Documentação OpenAPI:** [Dedoc Scramble](https://scramble.dedoc.co/) (OpenAPI / Swagger interativo)
* **Testes & Qualidade:** [Pest PHP v5](https://pestphp.com/), [Larastan / PHPStan](https://github.com/larastan/larastan) e [Laravel Pint](https://laravel.com/docs/pint)

---

## Padrões Arquiteturais e Decisões de Design

* **Single-Action Controllers:** Controllers enxutos e invocáveis (`__invoke`), delegando regras de negócio para **Actions** dedicadas.
* **Data Transfer Objects (DTOs):** Validação em camadas através de **FormRequests** convertidos diretamente para **DTOs** tipados com `spatie/laravel-data`.
* **UUIDs Públicos (`HasPublicUuid`):** Identificadores expostos publicamente via UUID para Route Model Binding, preservando chaves primárias inteiras (`bigserial`) para performance de indexação interna e foreign keys.
* **Resiliência no Provisionamento de Tenants:** O provisionamento de schema e migrations de cada tenant roda após o commit do banco central. Se o provisionamento falhar, a action remove o registro do tenant para não deixar registros órfãos nem prender os domínios solicitados.
* **Segurança contra Race Conditions:** Uso de `lockForUpdate` na deleção de domínios para garantir que nenhum tenant fique sem ao menos um domínio ativo.
* **Lock Distribuído em Deploys (`migrate:wait`):** O entrypoint dos containers utiliza `Cache::lock('deploy:migrate')->block()` para garantir que apenas uma réplica execute migrations e seeders por vez, enquanto as demais aguardam a liberação do lock antes de iniciar o boot do RoadRunner.
* **Strict Typing & Atributos PHP 8:** `declare(strict_types=1);` em todos os arquivos e uso de atributos nativos do Eloquent (`#[UsePolicy]`, `#[Fillable]`, `#[Hidden]`).

---

## Serviços e Portas Locais (Docker)

| Serviço | Porta Host | Porta Container | Descrição |
| :--- | :--- | :--- | :--- |
| **app** | `8001` | `8000` | API Laravel Octane + RoadRunner (com hot reload) |
| **horizon** | - | - | Worker de filas Redis com dashboard Horizon |
| **scheduler** | - | - | Processamento de tarefas agendadas (`schedule:work`) |
| **pgsql** | `8006` | `5432` | Banco de dados PostgreSQL 17 |
| **redis** | `8005` | `6379` | Cache, locks distribuídos e filas |

---

## Como Subir o Projeto Localmente

### Pré-requisitos
* [Docker](https://docs.docker.com/get-docker/) e [Docker Compose](https://docs.docker.com/compose/) instalados.

### 1. Clonar e Configurar Variáveis de Ambiente
```bash
cp .env.example .env
```

Edite o arquivo `.env` para ajustar senhas e configurações, principalmente:
```dotenv
SUPER_ADMIN_PASSWORD="sua-senha-segura-aqui"
CENTRAL_DOMAINS="127.0.0.1,localhost"
```

### 2. Construir e Iniciar os Containers
```bash
docker compose up -d --build
```

O entrypoint executará automaticamente as migrations centrais com lock distribuído e a sincronização das roles e permissions.

### 3. Gerar Chaves da Aplicação e JWT
```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan jwt:secret
```

### 4. Semear o Super Administrador
Para criar o primeiro usuário super-admin com base nas variáveis do `.env`:
```bash
docker compose exec app php artisan db:seed --class=Database\\Seeders\\Central\\SuperAdminSeeder
```

Ou crie novos usuários de forma interativa pelo console:
```bash
docker compose exec app php artisan user:create
```

---

## Documentação da API

A documentação OpenAPI interativa é gerada automaticamente pelo **Scramble** e fica disponível em:

 **[http://localhost:8001/docs/api](http://localhost:8001/docs/api)**

### Fluxo de Autenticação
1. Realize a autenticação em `POST /api/v1/auth/login` informando `email` e `password`.
2. Inclua o token retornado no cabeçalho das requisições subsequentes:
   ```http
   Authorization: Bearer {access_token}
   ```

---

## Níveis de Acesso e Permissões (RBAC)

O sistema possui controle hierárquico onde usuários só podem gerenciar contas de nível igual ou inferior ao seu:

| Role | Nível | Permissões |
| :--- | :---: | :--- |
| **`super-admin`** | 3 | Acesso irrestrito a todas as operações (incluindo exclusão definitiva). |
| **`admin`** | 2 | Gerenciamento completo de usuários, tenants e domínios, exceto exclusão (`*.delete`). |
| **`employee`** | 1 | Apenas visualização (`*.view`). |

> **Nota:** Políticas de segurança impedem que qualquer usuário desative ou exclua a própria conta.

---

## Qualidade de Código e Testes

### Executar a Suíte de Testes (Pest)
```bash
docker compose exec app php artisan test
```

### Análise Estática (PHPStan / Larastan)
```bash
docker compose exec app ./vendor/bin/phpstan analyse --memory-limit=512M
```

### Formatação de Código (Laravel Pint)
```bash
docker compose exec app ./vendor/bin/pint
```
Para apenas validar sem alterar arquivos:
```bash
docker compose exec app ./vendor/bin/pint --test
```

---

## 📄 Licença

Este projeto está sob a licença [MIT](https://opensource.org/licenses/MIT).

