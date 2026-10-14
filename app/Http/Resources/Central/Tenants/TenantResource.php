<?php

declare(strict_types=1);

namespace App\Http\Resources\Central\Tenants;

use App\Http\Resources\Central\Domains\DomainResource;
use App\Models\Central\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Tenant
 */
class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            /**
             * Identificador público do tenant, usado nas URLs.
             */
            'uuid' => $this->uuid,
            /**
             * Nome do tenant.
             *
             * @example Cliente X
             */
            'name' => $this->name,
            /**
             * Domínios pelos quais o tenant é acessado.
             */
            'domains' => DomainResource::collection($this->whenLoaded('domains')),
            /**
             * Data de criação.
             */
            'created_at' => $this->created_at,
            /**
             * Data da última alteração.
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
