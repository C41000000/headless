<?php

declare(strict_types=1);

namespace App\DTOs\Tenant\Auth;

use Spatie\LaravelData\Data;

/**
 * Dados já validados pelo LoginRequest do tenant.
 */
final class LoginDTO extends Data
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}
}
