<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Users;

use App\Models\Tenant\User;

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
