<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\VisualIdentity;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVisualIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $hexRegex = 'regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/';

        return [
            /**
             * Cor primária em formato hexadecimal (#RGB, #RRGGBB ou #RRGGBBAA).
             *
             * @example #0D6EFD
             */
            'primary_color' => ['sometimes', 'string', $hexRegex],

            /**
             * Cor secundária em formato hexadecimal (#RGB, #RRGGBB ou #RRGGBBAA).
             *
             * @example #6C757D
             */
            'secondary_color' => ['sometimes', 'string', $hexRegex],

            /**
             * Cor de destaque/acento em formato hexadecimal (opcional).
             *
             * @example #FFC107
             */
            'accent_color' => ['sometimes', 'nullable', 'string', $hexRegex],

            /**
             * URL do logotipo principal.
             *
             * @example https://storage.exemplo.com/logo.png
             */
            'logo_url' => ['sometimes', 'nullable', 'string', 'url', 'max:2048'],

            /**
             * URL do logotipo para tema escuro.
             *
             * @example https://storage.exemplo.com/logo-dark.png
             */
            'logo_dark_url' => ['sometimes', 'nullable', 'string', 'url', 'max:2048'],

            /**
             * URL do favicon do tenant.
             *
             * @example https://storage.exemplo.com/favicon.ico
             */
            'favicon_url' => ['sometimes', 'nullable', 'string', 'url', 'max:2048'],

            /**
             * Nome da fonte principal a ser aplicada pela interface.
             *
             * @example Inter, sans-serif
             */
            'font_family' => ['sometimes', 'nullable', 'string', 'max:100'],

            /**
             * Regras de CSS customizadas a serem injetadas pelo frontend.
             *
             * @example :root { --border-radius: 8px; }
             */
            'custom_css' => ['sometimes', 'nullable', 'string', 'max:10000'],
        ];
    }
}
