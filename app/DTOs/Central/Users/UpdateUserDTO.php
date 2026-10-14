<?php

declare(strict_types=1);

namespace App\DTOs\Central\Users;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateUserDTO extends Data
{
    public function __construct(
        public readonly string|Optional $name,
        public readonly string|Optional $email,
        public readonly bool|Optional $is_active
    ) {}
}
