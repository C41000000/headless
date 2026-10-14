<?php

declare(strict_types=1);

namespace App\Actions\Central\Domains;

use App\DTOs\Central\Domains\DomainDTO;
use App\Models\Central\Domain;

final readonly class UpdateDomainAction
{
    public function execute(Domain $domain, DomainDTO $dto): void
    {
        $domain->update($dto->toArray());
    }
}
