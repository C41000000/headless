<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant\Users;

use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * Nome do usuário do tenant.
             *
             * @example João Silva
             */
            'name' => $this->name,
            /**
             * Identificador público do usuário, usado nas URLs.
             */
            'uuid' => $this->uuid,
            /**
             * E-mail de login no tenant.
             *
             * @example joao@empresa.com
             */
            'email' => $this->email,
            /**
             * Se o usuário pode logar no tenant.
             */
            'is_active' => $this->is_active,
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
