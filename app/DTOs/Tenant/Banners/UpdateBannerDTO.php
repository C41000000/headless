<?php

declare(strict_types=1);

namespace App\DTOs\Tenant\Banners;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateBannerDTO extends Data
{
    public function __construct(
        public readonly string|Optional $image_url,
        public readonly string|null|Optional $title,
        public readonly string|null|Optional $subtitle,
        public readonly string|null|Optional $mobile_image_url,
        public readonly string|null|Optional $link_url,
        public readonly string|Optional $target,
        public readonly string|Optional $placement,
        public readonly int|Optional $order,
        public readonly bool|Optional $is_active,
        public readonly CarbonImmutable|null|Optional $starts_at,
        public readonly CarbonImmutable|null|Optional $ends_at,
    ) {}
}
