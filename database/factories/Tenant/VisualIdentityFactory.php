<?php

declare(strict_types=1);

namespace Database\Factories\Tenant;

use App\Models\Tenant\VisualIdentity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisualIdentity>
 */
class VisualIdentityFactory extends Factory
{
    protected $model = VisualIdentity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'primary_color' => '#0D6EFD',
            'secondary_color' => '#6C757D',
            'accent_color' => '#FFC107',
            'logo_url' => fake()->imageUrl(300, 100, 'business'),
            'logo_dark_url' => fake()->imageUrl(300, 100, 'business'),
            'favicon_url' => fake()->imageUrl(64, 64),
            'font_family' => 'Inter, sans-serif',
            'custom_css' => ':root { --brand-radius: 8px; }',
        ];
    }
}
