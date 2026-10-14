<?php

declare(strict_types=1);

namespace App\Http\Requests\Central\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /**
             * Novo nome do usuário.
             *
             * @example Maria Souza
             */
            'name' => ['sometimes', 'string', 'max:255'],
            /**
             * Novo e-mail de login. Precisa ser único.
             *
             * @example maria@example.com
             */
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->route('user')),
            ],
            /**
             * Ativa ou desativa o usuário. Para só inverter o status, prefira a rota de ativar/desativar, que bloqueia a autodesativação.
             *
             * @example true
             */
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
