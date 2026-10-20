<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Users;

use App\DTOs\Tenant\Users\CreateUserDTO;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;

final readonly class CreateUserAction
{
    public function execute(CreateUserDTO $dto): User
    {
        return DB::transaction(function () use ($dto): User {
            $user = User::query()->create($dto->except('role')->toArray());

            if ($dto->role !== null) {
                $user->syncRoles([$dto->role->value]);
            }

            return $user;
        });
    }
}
