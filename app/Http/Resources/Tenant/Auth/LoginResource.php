<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant\Auth;

use App\DTOs\Tenant\Auth\LoginResultDTO;
use App\Http\Resources\Tenant\Users\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property LoginResultDTO $resource
 */
class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * Token JWT gerado para a sessão do tenant.
             *
             * @example "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
             */
            'access_token' => $this->resource->token,

            /**
             * Tipo do token gerado.
             *
             * @example "Bearer"
             */
            'token_type' => 'Bearer',

            /**
             * Tempo de expiração do token em segundos.
             *
             * @example 3600
             */
            'expires_in' => $this->resource->expiresIn,

            /**
             * Dados do usuário autenticado no tenant.
             */
            'user' => UserResource::make($this->resource->user),
        ];
    }
}
