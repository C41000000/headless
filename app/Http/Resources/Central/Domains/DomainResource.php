<?php

declare(strict_types=1);

namespace App\Http\Resources\Central\Domains;

use App\Models\Central\Domain;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Domain
 */
class DomainResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            /**
             * Identificador público do domínio, usado nas URLs.
             */
            'uuid' => $this->uuid,
            /**
             * Hostname do domínio.
             *
             * @example clientex.app.com
             */
            'domain' => $this->domain,
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
