<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Policies\Tenant\VisualIdentityPolicy;
use App\Support\Traits\HasPublicUuid;
use Database\Factories\Tenant\VisualIdentityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'primary_color',
    'secondary_color',
    'accent_color',
    'logo_url',
    'logo_dark_url',
    'favicon_url',
    'font_family',
    'custom_css',
])]
#[UsePolicy(VisualIdentityPolicy::class)]
class VisualIdentity extends Model
{
    /** @use HasFactory<VisualIdentityFactory> */
    use HasFactory, HasPublicUuid;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'primary_color' => '#0D6EFD',
        'secondary_color' => '#6C757D',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): VisualIdentityFactory
    {
        return VisualIdentityFactory::new();
    }
}
