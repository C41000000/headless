<?php

declare(strict_types=1);

namespace App\Actions\Central\Tenants;

use App\DTOs\Central\Tenants\CreateTenantDTO;
use App\Models\Central\Tenant;
use Illuminate\Support\Facades\DB;
use Throwable;

final readonly class CreateTenantAction
{
    /**
     * Cria tenant e domínios numa transação. O banco do tenant é provisionado
     * depois do commit (TenancyServiceProvider); se isso falhar, o registro é
     * removido para os domínios ficarem livres para uma nova tentativa.
     *
     * @throws Throwable
     */
    public function execute(CreateTenantDTO $dto): Tenant
    {
        $tenant = null;

        try {
            return DB::transaction(function () use ($dto, &$tenant) {
                $tenant = Tenant::query()->create(['name' => $dto->name]);

                foreach ($dto->domains as $domain) {
                    $tenant->domains()->create(['domain' => $domain]);
                }

                return $tenant->load('domains');
            });
        } catch (Throwable $e) {
            // Falha no provisionamento (depois do commit): remove o registro. Se a
            // falha foi dentro da transação, o rollback já desfez tudo e o delete
            // não encontra nada.
            if ($tenant?->exists) {
                $tenant->forceDelete();
            }

            throw $e;
        }
    }
}
