<?php

declare(strict_types=1);

namespace App\Models\Central;

use App\Policies\Central\TenantPolicy;
use App\Support\Traits\HasPublicUuid;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as TenantModel;

/**
 * Soft delete: o tenant excluído para de resolver nos domínios (o resolver do
 * stancl consulta este model), mas o banco dele é mantido.
 */
#[UsePolicy(TenantPolicy::class)]
class Tenant extends TenantModel implements TenantWithDatabase
{
    use HasDatabase, HasDomains, HasPublicUuid, SoftDeletes;

    /**
     * Sobrescreve a relação do HasDomains (sem tipo de retorno) para o Larastan
     * reconhecer o model Domain.
     *
     * @return HasMany<Domain, $this>
     */
    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class, 'tenant_id');
    }

    /**
     * Colunas reais da tabela. Qualquer outro atributo o stancl guarda no JSON
     * "data", por isso os timestamps precisam estar aqui.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'uuid',
            'name',
            'created_at',
            'updated_at',
            'deleted_at',
        ];
    }
}
