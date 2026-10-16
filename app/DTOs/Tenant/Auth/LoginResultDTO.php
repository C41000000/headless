<?php

declare(strict_types=1);

namespace App\DTOs\Tenant\Auth;

use App\Models\Tenant\User;

/**
 * Resultado do LoginAction do tenant.
 */
final readonly class LoginResultDTO
{
    public function __construct(
        public string $token,
        public User $user,
        public int $expiresIn,
    ) {}
}
