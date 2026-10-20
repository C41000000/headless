<?php

declare(strict_types=1);

namespace App\Support\Enums\Tenant;

enum PermissionName: string
{
    case UsersView = 'users.view';
    case UsersCreate = 'users.create';
    case UsersUpdate = 'users.update';
    case UsersDelete = 'users.delete';
    case UsersToggleStatus = 'users.toggle-status';

    case VisualIdentityView = 'visual-identity.view';
    case VisualIdentityUpdate = 'visual-identity.update';

    case BannersView = 'banners.view';
    case BannersCreate = 'banners.create';
    case BannersUpdate = 'banners.update';
    case BannersDelete = 'banners.delete';
}
