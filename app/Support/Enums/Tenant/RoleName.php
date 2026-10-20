<?php

declare(strict_types=1);

namespace App\Support\Enums\Tenant;

enum RoleName: string
{
    case Admin = 'admin';
    case Employee = 'employee';

    /**
     * Nível na hierarquia do tenant: só se gerencia (edita, desativa, exclui) quem tem
     * nível igual ou menor. Usuário sem role conhecida fica no nível 0.
     */
    public function level(): int
    {
        return match ($this) {
            self::Admin => 2,
            self::Employee => 1,
        };
    }

    /**
     * Listas explícitas de permissões associadas a cada papel no tenant.
     *
     * @return list<PermissionName>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => PermissionName::cases(),

            self::Employee => [
                PermissionName::UsersView,
                PermissionName::VisualIdentityView,
                PermissionName::BannersView,
            ],
        };
    }
}
