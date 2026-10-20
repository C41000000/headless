<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Banners;

use App\DTOs\Tenant\Banners\CreateBannerDTO;
use App\Models\Tenant\Banner;

final readonly class CreateBannerAction
{
    public function execute(CreateBannerDTO $dto): Banner
    {
        return Banner::query()->create($dto->toArray());
    }
}
