<?php

declare(strict_types=1);

namespace App\Actions\Tenant\VisualIdentity;

use App\Models\Tenant\VisualIdentity;

final readonly class GetVisualIdentityAction
{
    /**
     * Retorna a identidade visual do tenant, criando uma com valores padrão caso ainda não exista.
     */
    public function execute(): VisualIdentity
    {
        return VisualIdentity::query()->firstOrCreate([], [
            'primary_color' => '#0D6EFD',
            'secondary_color' => '#6C757D',
        ]);
    }
}
