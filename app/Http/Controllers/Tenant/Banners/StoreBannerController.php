<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Banners;

use App\Actions\Tenant\Banners\CreateBannerAction;
use App\DTOs\Tenant\Banners\CreateBannerDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Banners\StoreBannerRequest;
use App\Http\Resources\Tenant\Banners\BannerResource;
use App\Models\Tenant\Banner;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Banners', 'Exibição e gerenciamento de banners do tenant.', weight: 13)]
final class StoreBannerController extends Controller
{
    /**
     * Criar novo banner
     *
     * Cadastra um novo banner no tenant com imagem, links, vigência e ordenação.
     *
     * Requer a permission `banners.create`.
     */
    public function __invoke(StoreBannerRequest $request, CreateBannerAction $action): BannerResource
    {
        Gate::authorize('create', Banner::class);

        $banner = $action->execute(CreateBannerDTO::from($request->validated()));

        /** @status 201 */
        return new BannerResource($banner);
    }
}
