<?php

declare(strict_types=1);

namespace App\DTOs\Central\Tenants;

use Spatie\LaravelData\Data;

/**
 * Dados já validados pelo CreateTenantRequest.
 */
final class CreateTenantDTO extends Data
{
    /**
     * @param  list<string>  $domains
     */
    public function __construct(
        public readonly string $name,
        public readonly array $domains,
    ) {}
}
