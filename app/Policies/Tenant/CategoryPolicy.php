<?php

declare(strict_types=1);

namespace App\Policies\Tenant;

use App\Models\Tenant\Category;
use App\Models\Tenant\User;
use App\Support\Enums\Tenant\PermissionName;

class CategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo(PermissionName::CategoriesView);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Category $model): bool
    {
        return $user->checkPermissionTo(PermissionName::CategoriesView);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo(PermissionName::CategoriesCreate);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Category $model): bool
    {
        return $user->checkPermissionTo(PermissionName::CategoriesUpdate);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Category $model): bool
    {
        return $user->checkPermissionTo(PermissionName::CategoriesDelete);
    }
}
