<?php

declare(strict_types=1);

namespace App\Actions\Central\Tenants;

use App\DTOs\Central\Tenants\UpdateTenantDTO;
use App\Models\Central\Tenant;

final readonly class UpdateTenantAction
{
    public function execute(Tenant $tenant, UpdateTenantDTO $dto): void
    {
        $tenant->update($dto->toArray());
    }
}
