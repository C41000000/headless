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
}
