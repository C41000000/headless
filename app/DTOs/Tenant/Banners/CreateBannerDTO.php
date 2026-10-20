<?php

declare(strict_types=1);

namespace App\DTOs\Tenant\Banners;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

final class CreateBannerDTO extends Data
{
    public function __construct(
        public readonly string $image_url,
        public readonly ?string $title = null,
        public readonly ?string $subtitle = null,
        public readonly ?string $mobile_image_url = null,
        public readonly ?string $link_url = null,
        public readonly string $target = '_self',
        public readonly string $placement = 'home',
        public readonly int $order = 0,
        public readonly bool $is_active = true,
        public readonly ?CarbonImmutable $starts_at = null,
        public readonly ?CarbonImmutable $ends_at = null,
    ) {}
}
