<?php

declare(strict_types=1);

namespace App\Policies\Central;

use App\Models\User;
use App\Support\Enums\PermissionName;
use App\Support\Enums\RoleName;

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
    public function create(User $user, ?RoleName $role = null): bool
    {
        return $user->checkPermissionTo(PermissionName::UsersCreate)
            && $this->canAssign($user, $role);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model, ?RoleName $role = null): bool
    {
        return $user->checkPermissionTo(PermissionName::UsersUpdate)
            && $this->outranksOrEquals($user, $model)
            && $this->canAssign($user, $role);
    }

    /**
     * Determine whether the user can activate/deactivate the model. Ninguém
     * altera o próprio status, para não se trancar fora do sistema.
     */
    public function toggleStatus(User $user, User $model): bool
    {
        return $user->isNot($model)
            && $user->checkPermissionTo(PermissionName::UsersToggleStatus)
            && $this->outranksOrEquals($user, $model);
    }

    /**
     * Determine whether the user can delete the model. Ninguém pode excluir a
     * própria conta, nem o super admin.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isNot($model)
            && $user->checkPermissionTo(PermissionName::UsersDelete)
            && $this->outranksOrEquals($user, $model);
    }

    /**
     * Super admin só é gerenciado por super admin; admin, por admin ou super admin.
     */
    /**
     * Só se atribui papel de nível igual ou menor que o próprio: admin não promove ninguém a super-admin.
     */
    private function canAssign(User $user, ?RoleName $role): bool
    {
        return $role === null || $role->level() <= $user->roleLevel();
    }

    private function outranksOrEquals(User $user, User $model): bool
    {
        return $user->roleLevel() >= $model->roleLevel();
    }
}
