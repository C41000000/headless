<?php

declare(strict_types=1);

namespace App\Actions\Central\Users;

use App\Models\User;

final readonly class ToggleUserStatusAction
{
    public function execute(User $user): User
    {
        $user->update([
            'is_active' => ! $user->is_active,
        ]);

        return $user->refresh();
    }
}
