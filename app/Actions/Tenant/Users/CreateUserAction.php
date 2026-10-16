<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Users;

use App\DTOs\Tenant\Users\CreateUserDTO;
use App\Models\Tenant\User;

final readonly class CreateUserAction
{
    public function execute(CreateUserDTO $dto): User
    {
        return User::query()->create($dto->toArray());
    }
}
