<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Users;

use App\Models\Tenant\User;

final readonly class DeleteUserAction
{
    public function execute(User $user): void
    {
        $user->delete();
    }
}
