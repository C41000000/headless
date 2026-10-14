#!/bin/sh
set -e

cd /var/www/html

# Só o serviço "app" deve migrar; horizon/scheduler sobem com RUN_MIGRATIONS=false
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    # Por enquanto só o banco central (database/migrations). Tenants ficam para depois
    # via "php artisan tenants:migrate".
    # migrate:wait (routes/console.php) segura um lock no Redis: só uma réplica migra
    # por vez e as demais esperam ela terminar antes de subir.
    # Depois das migrations, sincroniza roles/permissions com os enums (idempotente,
    # e remove as que saíram dos enums).
    attempts=0
    until php artisan migrate:wait --no-interaction --seeder='Database\Seeders\Central\RolesAndPermissionsSeeder'; do
        attempts=$((attempts + 1))
        if [ "$attempts" -ge "${MIGRATION_MAX_ATTEMPTS:-10}" ]; then
            echo "entrypoint: migrations falharam após ${attempts} tentativas" >&2
            exit 1
        fi
        echo "entrypoint: banco indisponível, nova tentativa em 3s (${attempts})..." >&2
        sleep 3
    done
fi

# Limpa caches de bootstrap. Não usamos optimize:clear porque ele também roda
# cache:clear, que apagaria locks/rate limits e depende do banco estar pronto.
php artisan config:clear --no-interaction
php artisan route:clear --no-interaction
php artisan view:clear --no-interaction
php artisan event:clear --no-interaction
php artisan clear-compiled --no-interaction

# Em produção, recompila os caches com as variáveis de ambiente do container
if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan optimize --no-interaction
fi

exec "$@"
