<?php

declare(strict_types=1);

namespace App\Policies\Tenant;

use App\Models\Tenant\Product;
use App\Models\Tenant\User;
use App\Support\Enums\Tenant\PermissionName;

class ProductPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo(PermissionName::ProductsView);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Product $model): bool
    {
        return $user->checkPermissionTo(PermissionName::ProductsView);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo(PermissionName::ProductsCreate);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Product $model): bool
    {
        return $user->checkPermissionTo(PermissionName::ProductsUpdate);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Product $model): bool
    {
        return $user->checkPermissionTo(PermissionName::ProductsDelete);
    }
}
