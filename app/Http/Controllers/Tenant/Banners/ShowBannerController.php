<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Banners;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Banners\BannerResource;
use App\Models\Tenant\Banner;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Banners', 'Exibição e gerenciamento de banners do tenant.', weight: 13)]
final class ShowBannerController extends Controller
{
    /**
     * Visualizar banner
     *
     * Retorna os detalhes de um banner específico do tenant pelo seu identificador público (UUID).
     *
     * Requer a permission `banners.view`.
     */
    public function __invoke(Banner $banner): BannerResource
    {
        Gate::authorize('view', $banner);

        return new BannerResource($banner);
    }
}
