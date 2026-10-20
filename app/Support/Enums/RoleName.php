<?php

declare(strict_types=1);

namespace App\Support\Enums;

enum RoleName: string
{
    case SuperAdmin = 'super-admin';
    case Admin = 'admin';
    case Employee = 'employee';

    /**
     * Nível na hierarquia: só se gerencia (edita, desativa, exclui) quem tem
     * nível igual ou menor. Usuário sem role conhecida fica no nível 0.
     */
    public function level(): int
    {
        return match ($this) {
            self::SuperAdmin => 3,
            self::Admin => 2,
            self::Employee => 1,
        };
    }

    /**
     * Roles que alguém do nível informado pode atribuir: só as de nível igual ou menor.
     *
     * @return list<self>
     */
    public static function assignableUpTo(int $level): array
    {
        return array_values(array_filter(self::cases(), fn (self $role) => $role->level() <= $level));
    }

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
