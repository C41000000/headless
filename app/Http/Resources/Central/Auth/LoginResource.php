<?php

declare(strict_types=1);

namespace App\Http\Resources\Central\Auth;

use App\Http\Resources\Central\Users\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
             * Token JWT gerado para a sessão.
             *
             * @example "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
             */
            'access_token' => $this->resource['token'],

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
            'expires_in' => $this->resource['expires_in'],

            /**
             * Dados do usuário autenticado.
             */
            'user' => UserResource::make($this->resource['user']),
        ];
    }
}
