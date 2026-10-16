<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\Users;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /**
             * Nome do usuário do tenant.
             *
             * @example João Silva
             */
            'name' => ['required', 'string', 'max:255'],
            /**
             * Senha de acesso, com no mínimo 8 caracteres.
             *
             * @example senha-segura-123
             */
            'password' => ['required', 'string', 'min:8'],
            /**
             * E-mail de login no tenant. Precisa ser único dentro do tenant.
             *
             * @example joao@empresa.com
             */
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        ];
    }
}
