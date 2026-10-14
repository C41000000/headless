<?php

declare(strict_types=1);

namespace App\Support\Traits;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

trait HasPublicUuid
{
    use HasUuids;

    /**
     * Determina as colunas que devem receber um UUID gerado automaticamente.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Define a coluna padrão para Route Model Binding.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
