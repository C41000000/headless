<?php

declare(strict_types=1);

namespace App\Http\Resources\Central\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Usuário autenticado, com o papel e as permissions efetivas para o front
 * decidir o que exibir.
 *
 * @mixin User
 */
class AuthUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            /**
             * Identificador público do usuário.
             */
            'uuid' => $this->uuid,
            /**
             * Nome do usuário.
             *
             * @example Maria Souza
             */
            'name' => $this->name,
            /**
             * E-mail de login.
             *
             * @example maria@example.com
             */
            'email' => $this->email,
            /**
             * Se o usuário pode logar.
             */
            'is_active' => $this->is_active,
            /**
             * Papel do usuário (o de maior nível). Null quando não tem papel.
             *
             * @example admin
             */
            'role' => $this->resource->highestRole()?->value,
            /**
             * Permissions efetivas do usuário (vindas do papel).
             *
             * @var list<string>
             *
             * @example ["users.view", "tenants.view"]
             */
            'permissions' => $this->resource->getAllPermissions()->pluck('name')->sort()->values()->all(),
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
