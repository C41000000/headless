<?php

declare(strict_types=1);

namespace App\DTOs\Central\Auth;

use Spatie\LaravelData\Data;

/**
 * Dados já validados pelo LoginRequest.
 */
final class LoginDTO extends Data
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}
}
