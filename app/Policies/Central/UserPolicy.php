<?php

declare(strict_types=1);

namespace App\Policies\Central;

use App\Models\User;
use App\Support\Enums\PermissionName;

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
        return $user->checkPermissionTo(PermissionName::UsersUpdate);
    }

    /**
     * Determine whether the user can delete the model. Ninguém pode excluir a
     * própria conta, nem o super admin.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->isNot($model) && $user->checkPermissionTo(PermissionName::UsersDelete);
    }

    public function ToggleStatus(User $user, User $model): bool
    {
        return $user->checkPermissionTo(PermissionName::UsersToggleStatus);
    }
}
