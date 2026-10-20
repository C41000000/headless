<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Users;

use App\DTOs\Tenant\Users\UpdateUserDTO;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;

final readonly class UpdateUserAction
{
    public function execute(User $user, UpdateUserDTO $dto): void
    {
        DB::transaction(function () use ($user, $dto): void {
            $user->update($dto->except('role')->toArray());

            // Um papel por usuário: a nova role substitui as anteriores
            if (! $dto->role instanceof Optional) {
                $user->syncRoles([$dto->role->value]);
            }
        });
    }
}
