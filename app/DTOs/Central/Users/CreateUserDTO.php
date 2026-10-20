<?php

declare(strict_types=1);

namespace App\DTOs\Central\Users;

use App\Support\Enums\RoleName;
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
        public readonly ?RoleName $role = null,
    ) {}
}
