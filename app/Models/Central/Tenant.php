<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Support\Traits\HasPublicUuid;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as TenantModel;

class Tenant extends TenantModel implements TenantWithDatabase
{
    use HasDatabase, HasDomains, HasPublicUuid;

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'uuid',
            'name',
        ];
    }
}
