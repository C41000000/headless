<?php

declare(strict_types=1);

namespace App\Support\Enums;

enum PermissionName: string
{
    case UsersView = 'users.view';
    case UsersCreate = 'users.create';
    case UsersUpdate = 'users.update';
    case UsersDelete = 'users.delete';
    case UsersToggleStatus = 'users.toggle-status';

    case TenantsView = 'tenants.view';
    case TenantsCreate = 'tenants.create';
    case TenantsUpdate = 'tenants.update';
    case TenantsDelete = 'tenants.delete';

    case DomainsView = 'domains.view';
    case DomainsCreate = 'domains.create';
    case DomainsUpdate = 'domains.update';
    case DomainsDelete = 'domains.delete';
}
