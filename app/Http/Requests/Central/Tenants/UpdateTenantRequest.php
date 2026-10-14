<?php

declare(strict_types=1);

namespace App\Http\Requests\Central\Tenants;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /**
             * Novo nome do tenant.
             *
             * @example Cliente X Ltda.
             */
            'name' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
