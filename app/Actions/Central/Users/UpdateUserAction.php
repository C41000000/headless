<?php

declare(strict_types=1);

namespace App\Actions\Central\Users;

use App\DTOs\Central\Users\UpdateUserDTO;
use App\Models\User;

final readonly class UpdateUserAction
{
    public function execute(User $user, UpdateUserDTO $dto): void
    {
        $user->update($dto->toArray());
    }
}
