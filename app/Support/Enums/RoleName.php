<?php

declare(strict_types=1);

namespace App\Support\Enums;

enum RoleName: string
{
    case SuperAdmin = 'super-admin';
    case Admin = 'admin';
    case Employee = 'employee';

    /**
     * Listas explícitas (exceto o super-admin): uma permission nova só chega ao
     * admin ou ao funcionário quando for adicionada aqui de propósito.
     *
     * @return list<PermissionName>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::SuperAdmin => PermissionName::cases(),

            // Gerencia tudo, mas não exclui: exclusão fica só com o super-admin
            self::Admin => [
                PermissionName::UsersView,
                PermissionName::UsersCreate,
                PermissionName::UsersUpdate,
                PermissionName::UsersToggleStatus,
                PermissionName::TenantsView,
                PermissionName::TenantsCreate,
                PermissionName::TenantsUpdate,
                PermissionName::DomainsView,
                PermissionName::DomainsCreate,
                PermissionName::DomainsUpdate,
            ],

            self::Employee => [
                PermissionName::UsersView,
                PermissionName::TenantsView,
                PermissionName::DomainsView,
            ],
        };
    }
}
