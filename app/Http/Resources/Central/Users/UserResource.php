<?php

declare(strict_types=1);

namespace App\Http\Resources\Central\Users;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            /**
             * Nome do usuário.
             *
             * @example Maria Souza
             */
            'name' => $this->name,
            /**
             * Identificador público do usuário, usado nas URLs.
             */
            'uuid' => $this->uuid,
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
