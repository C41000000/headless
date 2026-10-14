<?php

declare(strict_types=1);

namespace App\DTOs\Central\Auth;

use App\Models\User;

/**
 * Resultado do LoginAction. É um objeto (e não array) para o Scramble conseguir
 * inferir o tipo do LoginResource, e não estende Data porque só serve de saída.
 */
final readonly class LoginResultDTO
{
    public function __construct(
        public string $token,
        public User $user,
        public int $expiresIn,
    ) {}
}
