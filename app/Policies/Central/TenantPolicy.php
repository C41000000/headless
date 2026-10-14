<?php

declare(strict_types=1);

namespace App\Policies\Central;

use App\Models\Central\Tenant;
use App\Models\User;
use App\Support\Enums\PermissionName;

class TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo(PermissionName::TenantsView);
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return $user->checkPermissionTo(PermissionName::TenantsView);
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo(PermissionName::TenantsCreate);
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $user->checkPermissionTo(PermissionName::TenantsUpdate);
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $user->checkPermissionTo(PermissionName::TenantsDelete);
    }
}
