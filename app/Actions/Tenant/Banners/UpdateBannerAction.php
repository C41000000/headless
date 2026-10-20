<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Banners;

use App\DTOs\Tenant\Banners\UpdateBannerDTO;
use App\Models\Tenant\Banner;

final readonly class UpdateBannerAction
{
    public function execute(Banner $banner, UpdateBannerDTO $dto): Banner
    {
        $banner->update($dto->toArray());

        return $banner->fresh();
    }
}
