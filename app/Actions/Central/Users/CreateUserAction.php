<?php

declare(strict_types=1);

namespace App\Actions\Central\Users;

use App\DTOs\Central\Users\CreateUserDTO;
use App\Models\User;

final readonly class CreateUserAction
{
    public function execute(CreateUserDTO $dto): User
    {
        return User::query()->create($dto->toArray());
    }
}
