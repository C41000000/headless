<?php

declare(strict_types=1);

namespace App\DTOs\Central\Users;

use Spatie\LaravelData\Data;

/**
 * Dados já validados pelo CreateUserRequest.
 */
final class CreateUserDTO extends Data
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $name,
    ) {}
}
