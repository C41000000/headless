<?php

declare(strict_types=1);

namespace App\Actions\Central\Domains;

use App\DTOs\Central\Domains\DomainDTO;
use App\Models\Central\Domain;
use App\Models\Central\Tenant;

final readonly class CreateDomainAction
{
    public function execute(Tenant $tenant, DomainDTO $dto): Domain
    {
        /** @var Domain $domain */
        $domain = $tenant->domains()->create($dto->toArray());

        return $domain;
    }
}
