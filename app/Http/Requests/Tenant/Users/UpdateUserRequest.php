<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\Users;

use App\Models\Tenant\User;
use App\Support\Enums\Tenant\RoleName;
use Closure;
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
             * Novo nome do usuário do tenant.
             *
             * @example João Silva
             */
            'name' => ['sometimes', 'string', 'max:255'],
            /**
             * Novo e-mail de login no tenant. Precisa ser único dentro do tenant.
             *
             * @example joao@empresa.com
             */
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->route('user')),
            ],
            /**
             * Ativa ou desativa o usuário no tenant.
             *
             * @example true
             */
            'is_active' => ['sometimes', 'boolean'],
            /**
             * Novo papel. Só é possível atribuir papéis de nível igual ou menor que o seu, e ninguém altera o próprio papel.
             *
             * @example admin
             */
            'role' => [
                'sometimes',
                Rule::enum(RoleName::class),
                function (string $attribute, mixed $value, Closure $fail): void {
                    $target = $this->route('user');

                    // Evita alguém se rebaixar (ou o último admin sumir) sem querer
                    if ($target instanceof User && $target->is($this->user()) && $target->highestRole()?->value !== $value) {
                        $fail('Você não pode alterar o seu próprio papel.');
                    }
                },
            ],
        ];
    }
}
