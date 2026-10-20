<?php

declare(strict_types=1);

namespace App\Policies\Tenant;

use App\Models\Tenant\Banner;
use App\Models\Tenant\User;
use App\Support\Enums\Tenant\PermissionName;

class BannerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo(PermissionName::BannersView);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Banner $model): bool
    {
        return $user->checkPermissionTo(PermissionName::BannersView);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->checkPermissionTo(PermissionName::BannersCreate);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Banner $model): bool
    {
        return $user->checkPermissionTo(PermissionName::BannersUpdate);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Banner $model): bool
    {
        return $user->checkPermissionTo(PermissionName::BannersDelete);
    }
}
