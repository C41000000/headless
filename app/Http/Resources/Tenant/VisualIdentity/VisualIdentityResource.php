<?php

declare(strict_types=1);

namespace App\Http\Resources\Tenant\VisualIdentity;

use App\Models\Tenant\VisualIdentity;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VisualIdentity
 */
class VisualIdentityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * Identificador público da identidade visual.
             */
            'uuid' => $this->uuid,

            /**
             * Cor primária da marca.
             *
             * @example #0D6EFD
             */
            'primary_color' => $this->primary_color,

            /**
             * Cor secundária da marca.
             *
             * @example #6C757D
             */
            'secondary_color' => $this->secondary_color,

            /**
             * Cor de destaque/acento.
             *
             * @example #FFC107
             */
            'accent_color' => $this->accent_color,

            /**
             * URL do logotipo principal.
             *
             * @example https://storage.exemplo.com/logo.png
             */
            'logo_url' => $this->logo_url,

            /**
             * URL do logotipo para tema escuro.
             *
             * @example https://storage.exemplo.com/logo-dark.png
             */
            'logo_dark_url' => $this->logo_dark_url,

            /**
             * URL do favicon do tenant.
             *
             * @example https://storage.exemplo.com/favicon.ico
             */
            'favicon_url' => $this->favicon_url,

            /**
             * Família tipográfica configurada.
             *
             * @example Inter, sans-serif
             */
            'font_family' => $this->font_family,

            /**
             * CSS customizado injetado.
             */
            'custom_css' => $this->custom_css,

            /**
             * Data da última atualização.
             */
            'updated_at' => $this->updated_at,
        ];
    }
}
