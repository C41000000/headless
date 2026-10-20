<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Models\Tenant\VisualIdentity;
use Illuminate\Database\Seeder;

class VisualIdentitySeeder extends Seeder
{
    /**
     * Garante que todo novo tenant tenha ao menos um registro de identidade visual padrão.
     */
    public function run(): void
    {
        VisualIdentity::query()->firstOrCreate([], [
            'primary_color' => '#0D6EFD',
            'secondary_color' => '#6C757D',
            'accent_color' => '#FFC107',
            'font_family' => 'Inter, sans-serif',
        ]);
    }
}
