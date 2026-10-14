<?php

declare(strict_types=1);

namespace App\Actions\Central\Tenants;

use App\Models\Central\Tenant;

final readonly class DeleteTenantAction
{
    /**
     * Soft delete: o banco do tenant é mantido.
     */
    public function execute(Tenant $tenant): void
    {
        $tenant->delete();
    }
}
