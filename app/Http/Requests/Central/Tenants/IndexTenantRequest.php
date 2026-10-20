<?php

declare(strict_types=1);

namespace App\Http\Requests\Central\Tenants;

use Illuminate\Foundation\Http\FormRequest;

class IndexTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /**
             * Termo de busca: filtra por trecho do nome do tenant ou de um dos seus domínios (sem diferenciar maiúsculas).
             *
             * @example cliente
             */
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
