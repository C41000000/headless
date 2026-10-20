<?php

declare(strict_types=1);

namespace App\Http\Requests\Tenant\Banners;

use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
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
        return [
            /**
             * URL da imagem principal do banner.
             *
             * @example https://storage.exemplo.com/banners/hero.webp
             */
            'image_url' => ['required', 'string', 'url', 'max:2048'],

            /**
             * Título ou chamada do banner.
             *
             * @example Grande Promoção de Verão
             */
            'title' => ['nullable', 'string', 'max:255'],

            /**
             * Subtítulo ou texto descritivo secundário.
             *
             * @example Descontos de até 50% em toda a loja.
             */
            'subtitle' => ['nullable', 'string', 'max:1000'],

            /**
             * URL da imagem otimizada para dispositivos móveis (opcional).
             *
             * @example https://storage.exemplo.com/banners/hero-mobile.webp
             */
            'mobile_image_url' => ['nullable', 'string', 'url', 'max:2048'],

            /**
             * URL de destino ao clicar no banner (opcional).
             *
             * @example https://empresa.com/promocao
             */
            'link_url' => ['nullable', 'string', 'url', 'max:2048'],

            /**
             * Destino do link: '_self' (mesma aba) ou '_blank' (nova aba).
             *
             * @example _blank
             */
            'target' => ['sometimes', 'string', 'in:_self,_blank'],

            /**
             * Área ou identificador de posição do banner (ex: home, sidebar, footer, checkout).
             *
             * @example home
             */
            'placement' => ['sometimes', 'string', 'max:50'],

            /**
             * Ordem de exibição do banner (números menores aparecem primeiro).
             *
             * @example 0
             */
            'order' => ['sometimes', 'integer', 'min:0'],

            /**
             * Se o banner está ativado.
             *
             * @example true
             */
            'is_active' => ['sometimes', 'boolean'],

            /**
             * Data/hora de início da vigência do banner (ISO 8601).
             *
             * @example 2026-10-20T00:00:00Z
             */
            'starts_at' => ['nullable', 'date'],

            /**
             * Data/hora de término da vigência do banner (ISO 8601).
             *
             * @example 2026-12-31T23:59:59Z
             */
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}
