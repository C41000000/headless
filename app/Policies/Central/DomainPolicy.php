<?php

declare(strict_types=1);

namespace App\Policies\Central;

use App\Models\Central\Domain;
use App\Models\User;
use App\Support\Enums\PermissionName;

class DomainPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->checkPermissionTo(PermissionName::DomainsView);
    }

    public function view(User $user, Domain $domain): bool
    {
        return $user->checkPermissionTo(PermissionName::DomainsView);
    }

    public function create(User $user): bool
    {
        return $user->checkPermissionTo(PermissionName::DomainsCreate);
    }

    public function update(User $user, Domain $domain): bool
    {
        return $user->checkPermissionTo(PermissionName::DomainsUpdate);
    }

    public function delete(User $user, Domain $domain): bool
    {
        return $user->checkPermissionTo(PermissionName::DomainsDelete);
    }
}
