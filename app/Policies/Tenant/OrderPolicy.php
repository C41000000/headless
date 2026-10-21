<?php

declare(strict_types=1);

namespace App\Policies\Tenant;

use App\Models\Tenant\Order;
use App\Models\Tenant\User;
use App\Support\Enums\Tenant\PermissionName;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo(PermissionName::OrdersView);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $model): bool
    {
        return $user->checkPermissionTo(PermissionName::OrdersView);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $model): bool
    {
        return $user->checkPermissionTo(PermissionName::OrdersUpdate);
    }
}
