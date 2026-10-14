<?php

declare(strict_types=1);

namespace App\DTOs\Central\Domains;

use Spatie\LaravelData\Data;

/**
 * Dados já validados pelo DomainRequest.
 */
final class DomainDTO extends Data
{
    public function __construct(
        public readonly string $domain,
    ) {}
}
