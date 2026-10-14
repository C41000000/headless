<?php

declare(strict_types=1);

namespace App\Actions\Central\Domains;

use App\Models\Central\Domain;
use App\Models\Central\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final readonly class DeleteDomainAction
{
    /**
     * Um tenant sem domínio fica inacessível, então o último não pode sair.
     *
     * @throws ValidationException
     */
    public function execute(Domain $domain): void
    {
        DB::transaction(function () use ($domain) {
            // Trava o tenant para duas exclusões simultâneas não removerem os dois últimos
            Tenant::query()->whereKey($domain->tenant_id)->lockForUpdate()->first();

            if (Domain::query()->where('tenant_id', $domain->tenant_id)->count() <= 1) {
                throw ValidationException::withMessages([
                    'domain' => ['O tenant precisa de ao menos um domínio.'],
                ]);
            }

            $domain->delete();
        });
    }
}
