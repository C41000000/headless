<?php

declare(strict_types=1);

namespace App\Http\Requests\Central\Tenants;

use App\Rules\TenantDomain;
use Illuminate\Foundation\Http\FormRequest;

class CreateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /**
             * Nome do tenant.
             *
             * @example Cliente X
             */
            'name' => ['required', 'string', 'max:255'],
            /**
             * Domínios do tenant, ao menos um. Cada um precisa ser um hostname completo, único na plataforma e diferente dos domínios centrais. São salvos em minúsculas.
             *
             * @example ["clientex.app.com"]
             */
            'domains' => ['required', 'array', 'min:1'],
            'domains.*' => ['required', 'string', 'distinct', new TenantDomain, 'unique:domains,domain'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_array($this->input('domains'))) {
            $this->merge([
                'domains' => array_map(
                    fn (mixed $domain) => is_string($domain) ? mb_strtolower(trim($domain)) : $domain,
                    $this->input('domains'),
                ),
            ]);
        }
    }
}
