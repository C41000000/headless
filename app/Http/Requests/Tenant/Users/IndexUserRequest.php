<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\Users;

use App\Support\Enums\Tenant\RoleName;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /**
             * Termo de busca: filtra por trecho do nome ou do e-mail (sem diferenciar maiúsculas).
             *
             * @example maria
             */
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            /**
             * Filtra pelo papel do usuário.
             *
             * @example admin
             */
            'role' => ['sometimes', 'nullable', Rule::enum(RoleName::class)],
            /**
             * Filtra por status: 1 para ativos, 0 para inativos.
             *
             * @example 1
             */
            'is_active' => ['sometimes', 'nullable', 'boolean'],
        ];
    }
}
