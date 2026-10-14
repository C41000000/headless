<?php

declare(strict_types=1);

namespace App\Http\Requests\Central\Domains;

use App\Models\Central\Domain;
use App\Rules\TenantDomain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Usado na criação e na edição: nos dois casos o corpo é só o domínio.
 */
class DomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Domain|null $domain */
        $domain = $this->route('domain');

        return [
            /**
             * Hostname completo do domínio, único na plataforma e diferente dos domínios centrais. É salvo em minúsculas.
             *
             * @example clientex.app.com
             */
            'domain' => ['required', 'string', new TenantDomain, Rule::unique('domains', 'domain')->ignore($domain?->id)],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('domain'))) {
            $this->merge(['domain' => mb_strtolower(trim($this->input('domain')))]);
        }
    }
}
