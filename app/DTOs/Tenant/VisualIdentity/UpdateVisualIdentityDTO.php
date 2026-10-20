<?php

declare(strict_types=1);

namespace App\DTOs\Tenant\VisualIdentity;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

final class UpdateVisualIdentityDTO extends Data
{
    public function __construct(
        public readonly string|Optional $primary_color,
        public readonly string|Optional $secondary_color,
        public readonly string|null|Optional $accent_color,
        public readonly string|null|Optional $logo_url,
        public readonly string|null|Optional $logo_dark_url,
        public readonly string|null|Optional $favicon_url,
        public readonly string|null|Optional $font_family,
        public readonly string|null|Optional $custom_css,
    ) {}
}
