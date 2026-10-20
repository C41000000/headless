<?php

declare(strict_types=1);

namespace App\Models\Tenant;

use App\Policies\Tenant\BannerPolicy;
use App\Support\Traits\HasPublicUuid;
use Database\Factories\Tenant\BannerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'title',
    'subtitle',
    'image_url',
    'mobile_image_url',
    'link_url',
    'target',
    'placement',
    'order',
    'is_active',
    'starts_at',
    'ends_at',
])]
#[UsePolicy(BannerPolicy::class)]
class Banner extends Model
{
    /** @use HasFactory<BannerFactory> */
    use HasFactory, HasPublicUuid, SoftDeletes;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'target' => '_self',
        'placement' => 'home',
        'order' => 0,
        'is_active' => true,
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): BannerFactory
    {
        return BannerFactory::new();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * Scope para retornar apenas banners ativos e dentro da vigência programada.
     *
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $now = now();

        $query->where('is_active', true)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }

    /**
     * Scope para filtrar pela posição/local de exibição.
     *
     * @param  Builder<self>  $query
     */
    public function scopePlacement(Builder $query, string $placement): void
    {
        $query->where('placement', $placement);
    }

    /**
     * Scope para ordenação de exibição.
     *
     * @param  Builder<self>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('order')->orderBy('id');
    }
}
