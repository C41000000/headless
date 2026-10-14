<?php

declare(strict_types=1);

namespace App\Actions\Central\Users;

use App\Models\User;

final readonly class DeleteUserAction
{
    public function execute(User $user): void
    {
        $user->delete();
    }
}
