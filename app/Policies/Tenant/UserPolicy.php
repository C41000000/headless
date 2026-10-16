<?php

declare(strict_types=1);

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use App\Support\Enums\Tenant\PermissionName;
use App\Support\Enums\Tenant\RoleName;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo(PermissionName::UsersView);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->checkPermissionTo(PermissionName::UsersView);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo(PermissionName::UsersCreate);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->checkPermissionTo(PermissionName::UsersUpdate)
            && $this->outranksOrEquals($user, $model);
    }

    /**
     * Determine whether the user can activate/deactivate the model. Ninguém
     * altera o próprio status, para não se trancar fora do tenant.
     */
    public function toggleStatus(User $user, User $model): bool
    {
        return $user->isNot($model)
            && $user->checkPermissionTo(PermissionName::UsersToggleStatus)
            && $this->outranksOrEquals($user, $model);
    }

    /**
     * Determine whether the user can delete the model. Ninguém pode excluir a
     * própria conta.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isNot($model)
            && $user->checkPermissionTo(PermissionName::UsersDelete)
            && $this->outranksOrEquals($user, $model);
    }

    /**
     * Admin só é gerenciado por admin.
     */
    private function outranksOrEquals(User $user, User $model): bool
    {
        return $this->level($user) >= $this->level($model);
    }

    private function level(User $user): int
    {
        return $user->loadMissing('roles')->getRoleNames()
            ->map(fn (string $name) => RoleName::tryFrom($name)?->level() ?? 0)
            ->max() ?? 0;
    }
}
