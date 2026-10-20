<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Banners;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Banners\BannerResource;
use App\Models\Tenant\Banner;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

#[Group('Tenant - Banners', 'Exibição e gerenciamento de banners do tenant.', weight: 13)]
final class IndexBannerController extends Controller
{
    /**
     * Listar todos os banners (Painel Administrativo)
     *
     * Retorna a listagem completa de banners cadastrados no tenant, com suporte a filtros de placement e status.
     *
     * Requer a permission `banners.view`.
     */
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Banner::class);

        $query = Banner::query()->ordered();

        if ($request->filled('placement')) {
            $query->placement((string) $request->query('placement'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return BannerResource::collection($query->get());
    }
}
