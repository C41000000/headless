<?php

declare(strict_types=1);

namespace App\Policies\Tenant;

use App\Models\Tenant\User;
use App\Models\Tenant\VisualIdentity;
use App\Support\Enums\Tenant\PermissionName;

class VisualIdentityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo(PermissionName::VisualIdentityView);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, VisualIdentity $model): bool
    {
        return $user->checkPermissionTo(PermissionName::VisualIdentityView);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, VisualIdentity $model): bool
    {
        return $user->checkPermissionTo(PermissionName::VisualIdentityUpdate);
    }
}
