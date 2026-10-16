<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Users;

use App\DTOs\Tenant\Users\UpdateUserDTO;
use App\Models\Tenant\User;

final readonly class UpdateUserAction
{
    public function execute(User $user, UpdateUserDTO $dto): void
    {
        $user->update($dto->toArray());
    }
}
