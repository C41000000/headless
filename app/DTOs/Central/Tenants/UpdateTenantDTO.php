<?php

declare(strict_types=1);

namespace App\DTOs\Central\Tenants;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/**
 * Dados já validados pelo UpdateTenantRequest.
 */
final class UpdateTenantDTO extends Data
{
    public function __construct(
        public readonly string|Optional $name,
    ) {}
}
