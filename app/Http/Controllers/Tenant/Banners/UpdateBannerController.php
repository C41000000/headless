<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Banners;

use App\Actions\Tenant\Banners\UpdateBannerAction;
use App\DTOs\Tenant\Banners\UpdateBannerDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Banners\UpdateBannerRequest;
use App\Http\Resources\Tenant\Banners\BannerResource;
use App\Models\Tenant\Banner;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Banners', 'Exibição e gerenciamento de banners do tenant.', weight: 13)]
final class UpdateBannerController extends Controller
{
    /**
     * Atualizar banner
     *
     * Atualiza os campos de imagem, links, vigência, ordenação ou status do banner.
     *
     * Requer a permission `banners.update`.
     */
    public function __invoke(UpdateBannerRequest $request, Banner $banner, UpdateBannerAction $action): BannerResource
    {
        Gate::authorize('update', $banner);

        $updated = $action->execute($banner, UpdateBannerDTO::from($request->validated()));

        return new BannerResource($updated);
    }
}
