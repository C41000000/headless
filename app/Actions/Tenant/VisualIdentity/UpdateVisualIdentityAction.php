<?php

declare(strict_types=1);

namespace App\Actions\Tenant\VisualIdentity;

use App\DTOs\Tenant\VisualIdentity\UpdateVisualIdentityDTO;
use App\Models\Tenant\VisualIdentity;

final readonly class UpdateVisualIdentityAction
{
    public function __construct(
        private GetVisualIdentityAction $getAction,
    ) {}

    public function execute(UpdateVisualIdentityDTO $dto): VisualIdentity
    {
        $visualIdentity = $this->getAction->execute();

        $visualIdentity->update($dto->toArray());

        return $visualIdentity->fresh();
    }
}
