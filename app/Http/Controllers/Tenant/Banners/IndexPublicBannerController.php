<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Banners;

use App\Http\Controllers\Controller;
use App\Http\Resources\Tenant\Banners\BannerResource;
use App\Models\Tenant\Banner;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Tenant - Banners', 'Exibição e gerenciamento de banners do tenant.', weight: 13)]
final class IndexPublicBannerController extends Controller
{
    /**
     * Listar banners públicos ativos
     *
     * Retorna a lista de banners atualmente ativos e dentro da vigência programada, ordenados pela ordem definida.
     * Pode ser filtrado pela área/posição desejada (ex: ?placement=home).
     */
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $query = Banner::query()
            ->active()
            ->ordered();

        if ($request->filled('placement')) {
            $query->placement((string) $request->query('placement'));
        }

        return BannerResource::collection($query->get());
    }
}
