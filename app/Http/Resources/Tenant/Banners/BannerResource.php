<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant\Banners;

use App\Models\Tenant\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Banner
 */
class BannerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * Identificador público do banner.
             */
            'uuid' => $this->uuid,

            /**
             * Título ou chamada do banner.
             *
             * @example Grande Promoção de Verão
             */
            'title' => $this->title,

            /**
             * Subtítulo do banner.
             *
             * @example Descontos de até 50%
             */
            'subtitle' => $this->subtitle,

            /**
             * URL da imagem principal.
             *
             * @example https://storage.exemplo.com/banners/hero.webp
             */
            'image_url' => $this->image_url,

            /**
             * URL da imagem otimizada para dispositivos móveis.
             *
             * @example https://storage.exemplo.com/banners/hero-mobile.webp
             */
            'mobile_image_url' => $this->mobile_image_url,

            /**
             * URL de destino ao clicar no banner.
             *
             * @example https://empresa.com/promocao
             */
            'link_url' => $this->link_url,

            /**
             * Alvo do link ('_self' ou '_blank').
             *
             * @example _blank
             */
            'target' => $this->target,

            /**
             * Posição ou localização de exibição do banner.
             *
             * @example home
             */
            'placement' => $this->placement,

            /**
             * Ordem de exibição relativa a outros banners.
             *
             * @example 0
             */
            'order' => $this->order,

            /**
             * Status de ativação do banner.
             */
            'is_active' => $this->is_active,

            /**
             * Início do período de vigência.
             */
            'starts_at' => $this->starts_at,

            /**
             * Término do período de vigência.
             */
            'ends_at' => $this->ends_at,

            /**
             * Data de criação.
             */
            'created_at' => $this->created_at,

            /**
             * Data da última atualização.
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
