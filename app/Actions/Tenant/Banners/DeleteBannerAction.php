<?php

declare(strict_types=1);

namespace App\Actions\Tenant\Banners;

use App\Models\Tenant\Banner;

final readonly class DeleteBannerAction
{
    public function execute(Banner $banner): void
    {
        $banner->delete();
    }
}
