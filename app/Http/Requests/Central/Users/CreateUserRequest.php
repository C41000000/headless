<?php

declare(strict_types=1);

namespace App\Http\Requests\Central\Users;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            /**
             * Nome do usuário.
             *
             * @example Maria Souza
             */
            'name' => ['required', 'string'],
            /**
             * Senha de acesso, com no mínimo 8 caracteres.
             *
             * @example senha-segura-123
             */
            'password' => ['required', 'string', 'min:8'],
            /**
             * E-mail de login. Precisa ser único.
             *
             * @example maria@example.com
             */
            'email' => ['required', 'string', 'email', 'unique:users,email'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
